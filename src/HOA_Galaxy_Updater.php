<?php

declare(strict_types=1);

namespace HOA\Galaxy;

class HOA_Galaxy_Updater {
    private string $file;
    private ?array $plugin = null;
    private string $basename;
    private bool $active;
    private string $username;
    private string $repository;
    private ?string $authorize_token = null;
    private ?array $github_response = null;
    private array $settings;

    public function __construct(string $file) {
        $this->file = $file;
        $this->set_plugin_properties();
        $this->settings = get_option('hoa_galaxy_settings', []);
    }

    public function set_plugin_properties(): void {
        $plugin_data = get_plugin_data($this->file);
        if (is_array($plugin_data)) {
            $this->plugin = $plugin_data;
            $this->basename = plugin_basename($this->file);
            $this->active = is_plugin_active($this->basename);
        } else {
            // Handle case where plugin data is not an array (e.g., plugin not fully loaded yet)
            $this->plugin = []; // Initialize as empty array to prevent further errors
            $this->basename = plugin_basename($this->file);
            $this->active = false;
        }
    }

    public function set_username(string $username): void {
        $this->username = $username;
    }

    public function set_repository(string $repository): void {
        $this->repository = $repository;
    }

    public function authorize(?string $token): void {
        $this->authorize_token = $token;
    }

    private function get_repository_info(): void {
        if (null !== $this->github_response) {
            return;
        }

        $request_uri = sprintf('https://api.github.com/repos/%s/%s/releases', $this->username, $this->repository);
        if ($this->authorize_token) {
            $request_uri = add_query_arg('access_token', $this->authorize_token, $request_uri);
        }
        
        $response = wp_remote_get($request_uri);
        if (is_wp_error($response)) {
            return;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (is_array($data)) {
            $this->github_response = current($data);
        }
    }

    public function initialize(): void {
        add_filter('pre_set_site_transient_update_plugins', [$this, 'modify_transient']);
        add_filter('plugins_api', [$this, 'plugin_popup'], 10, 3);
        add_filter('upgrader_post_install', [$this, 'after_install'], 10, 3);
    }

    public function modify_transient($transient) {
        $this->set_plugin_properties(); // Ensure properties are set
        
        // Only proceed if auto-updates are enabled
        if (!($this->settings['auto_updates_enabled'] ?? false)) {
            return $transient;
        }

        if (property_exists($transient, 'checked') && $transient->checked) {
            $this->get_repository_info();

            if (isset($this->github_response['tag_name']) && version_compare($this->plugin['Version'], $this->github_response['tag_name'], '<')) {
                $transient->response[$this->basename] = (object) [
                    'slug' => $this->basename,
                    'new_version' => $this->github_response['tag_name'],
                    'url' => $this->plugin['PluginURI'],
                    'package' => $this->github_response['zipball_url'],
                ];
            }
        }
        return $transient;
    }

    public function plugin_popup($result, string $action, object $args) {
        if (!empty($args->slug) && $args->slug === $this->basename) {
            $this->get_repository_info();

            if (!isset($this->github_response['tag_name'])) {
                return $result;
            }

            return (object) [
                'name' => $this->plugin['Name'],
                'slug' => $this->basename,
                'version' => $this->github_response['tag_name'],
                'author' => $this->plugin['AuthorName'],
                'author_profile' => $this->plugin['AuthorURI'],
                'last_updated' => $this->github_response['published_at'],
                'homepage' => $this->plugin['PluginURI'],
                'short_description' => $this->plugin['Description'],
                'sections' => [
                    'Description' => $this->plugin['Description'],
                    'Updates' => $this->github_response['body'],
                ],
                'download_link' => $this->github_response['zipball_url'],
            ];
        }
        return $result;
    }

    public function after_install($response, array $hook_extra, array $result): array {
        global $wp_filesystem;
        $install_directory = plugin_dir_path($this->file);
        $wp_filesystem->move($result['destination'], $install_directory);
        $result['destination'] = $install_directory;

        if ($this->active) {
            activate_plugin($this->basename);
        }
        return $result;
    }
}