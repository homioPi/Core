<?php 
    namespace HomioPi\Extensions;

    use \HomioPi\Interfaces\defaultInterface;

    class Extension extends defaultInterface {
        protected $dir;

        public function __construct($id) {
            $this->id  = $id;
            $this->dir = DIR_EXTENSIONS."/{$this->id}";
        }

        public function getProperties() {
			return \HomioPi\Extensions\get($this->id);
        }

        public function setProperties(array $properties) {
            $current_properties = $this->getProperties();

            $properties = array_replace_recursive($current_properties, $properties);

            return file_put_contents("{$this->dir}/manifest.json", json_encode($properties));
        }

        public function uninstall() {      
            // Return if extension is not installed
            if(is_null($this->getProperties())) {
                return false;
            }

            if(!isset($this->dir)) {
                return false;
            }

            // Delete the manifest file first to prevent the extension
            // from being loaded while deleting the remaining files
            @unlink("{$this->dir}/manifest.json");

            // Delete the remaining files
            return rmtree($this->dir);
        }

        public function loadFeatureComponent($group, $feature, $component) {
            if($component == 'main') {
                $feature_component_path = glob("{$this->dir}/features/{$group}/{$feature}/main.*")[0] ?? null;
            } else {
                $feature_component_path = "{$this->dir}/features/{$group}/{$feature}/{$component}";
            }

            // Return empty string if feature component does not exist
            if(!isset($feature_component_path) || !file_exists($feature_component_path)) {
                return '';
            }

            // Will return null if feature component is not executable
            $command = script_name_to_shell_cmd($feature_component_path);

            // Run feature component if it is executable, return file contents otherwise
            if(isset($command)) {
                execute($command, $res, 60);
            } else {
                $res = file_get_contents($feature_component_path);
            }

            return $res;
        }
        
        public function translate($key, $replacements = null, $fallback = null) {
            // Use user locale if it's supported, use en_US otherwise
            $user_locale = \HomioPi\Users\CurrentUser::getSetting('locale');

            $locale_file = "{$this->dir}/assets/locale/{$user_locale}.json";
            if(!file_exists($locale_file)) {
                $locale_file = "{$this->dir}/assets/locale/en_US.json";
            }

            // Load translations
            $translations = file_get_json($locale_file) ?? [];

            return \HomioPi\Locale\translate($key, $replacements, $fallback, $translations);
        }
    }

    function get_from_server($extension_id) {
        $extensions = \HomioPi\Extensions\get_all_from_server();

        foreach ($extensions as $extension) {
            if($extension['id'] == $extension_id) {
                return $extension;
            }
        }

        return null;
    }

    function get_all_from_server($category = 'none', $filter = null) {
        if($category == 'installed') {
            return [];
        }

        $client = new \GuzzleHttp\Client();
        $res = [];

        // Fetch catalogue
        $response = $client->request('GET', \HomioPi\Config\get('extensions_catalogue', 'servers'));
        $catalogue = json_decode($response->getBody(), true);

        foreach ($catalogue['items'] as $extension) {
            if(
                isset($filter) &&
                stripos($extension['manifest']['name'], $filter) === false && 
                stripos($extension['manifest']['description'], $filter) === false
            ) {
                continue;
            }

            $extension['repository']['pushed_ago'] = \HomioPi\Locale\date_format('best,best', $extension['repository']['pushed_at']);

            $res[] = [
                'id'          => $extension['id'] ?? null,
                'name'        => $extension['manifest']['name'] ?? null,
                'description' => $extension['manifest']['description'] ?? null,
                'repository'  => $extension['repository'],
                'installed'   => !is_null(\HomioPi\Extensions\get($extension['id'])),
                'enabled'     => \HomioPi\Extensions\get($extension['id'])['enabled'] ?? false,
                'verified'    => $extension['verified'] ?? false
            ];
        }

        return $res;
    }
    
    // Returns an array of all features across all installed extensions for a specific group
    function list_all_features($group, $include_disabled = false) {
        $manifests = \HomioPi\Extensions\get_all();

        $res = [];
        foreach ($manifests as $extension_id => $manifest) {
            if(!isset($manifest['features'][$group])) {
                continue;
            }

            foreach ($manifest['features'][$group] as $feature_id) {
                // Continue if feature folder does not exist
                if(!file_exists(DIR_EXTENSIONS."/{$extension_id}/features/{$group}/{$feature_id}")) {
                    continue;
                }

                $feature['id'] = $feature_id;
                array_push($res, [
                    'extension_id' => $extension_id,
                    'feature' => $feature
                ]);
            }
        }

        return $res;
    }

    // Returns the manifest for an installed extension
    function get($extension_id) {
        $manifest_file = DIR_EXTENSIONS."/{$extension_id}/manifest.json";

        // Return if the extension is not installed
        if(!file_exists($manifest_file)) {
            return null;
        }

        // Read the manifest
        $manifest = file_get_json($manifest_file);

        $manifest['enabled'] = (!isset($manifest['enabled']) || $manifest['enabled'] !== false);

        return $manifest;
    }

    // Returns an array of the manifests for every installed extension
    function get_all($include_disabled = false) {
        $manifest_files = glob(DIR_EXTENSIONS.'/*/manifest.json');

        $res = [];
        foreach ($manifest_files as $manifest_file) {
            $extension_id = basename(dirname($manifest_file));

            $manifest = \HomioPi\Extensions\get($extension_id);
            if(!isset($manifest)) {
                continue;
            }

            // Continue if extension is disabled
            if(isset($manifest['enabled']) && $manifest['enabled'] === false && !$include_disabled) {
                continue;
            }

            $res[$extension_id] = $manifest;
        }

        return $res;
    }

    function install($extension_id) {
        $extension = \HomioPi\Extensions\get_from_server($extension_id);

        if(!isset($extension)) {
            return 'The selected extension was not found.';
        }

        if($extension['installed']) {
            return 'The selected extension is already installed.';
        }

        // Create HTTP client
        $client = new \GuzzleHttp\Client();

        // Check if HomioPi_extension.json file exists
        $res = $client->request('GET', "https://raw.githubusercontent.com/{$extension['repository']['full_name']}/{$extension['repository']['default_branch']}/HomioPi_extension.json", [
            'http_errors'    => false,
            'decode_content' => 'json'
        ]);

        if($res->getStatusCode() != 200) {
            return 'The selected repository is not a HomioPi extension.';
        }

        exec("git --version", $git_version);
        if(strpos($git_version[0] ?? null, 'git version') === false) {
            return 'Git was not found on your machine.';
        }

        $destination  = DIR_EXTENSIONS."/{$extension_id}";
        $command      = "git clone -b {$extension['repository']['default_branch']} https://github.com/{$extension['repository']['full_name']} {$destination}";

        // End script automatically after 25 minutes
        set_time_limit(1500);

        // Clone the selected branch to the local directory, which is
        // not allowed to take more than 20 minutes
        $exit_code = execute($command, $output, 1200);
        
        if($exit_code != 0 && $exit_code != 1) {
            return 'Failed to clone the repository';
        }

        // Throw an error if HomioPi_extension.json file was not cloned
        if(!file_exists($destination.'/HomioPi_extension.json')) {
            return 'The repository was cloned, but a manifest file could not be found.';
        }
        
        // Rename the manifest file so the extension can be loaded
        rename($destination.'/HomioPi_extension.json', $destination.'/manifest.json');

        return true;
    }

    // function get_disabled_features($scopes = []) {
    //     $defined_features = array_merge(get_defined_features(false)['internal'], get_defined_features(false)['user']);

    //     $disabled_features = array_filter($defined_features, function($v) use ($scopes) {
    //         if(
    //             str_starts_with($v, 'str') ||
    //             str_starts_with($v, 'substr') ||
    //             str_starts_with($v, 'array_') ||
    //             str_starts_with($v, 'preg_') ||
    //             str_starts_with($v, 'html') ||

    //             str_contains($v, 'sleep') ||
    //             str_contains($v, '_exists') ||
    //             str_contains($v, 'is_') ||
    //             str_contains($v, 'timezone') ||

    //             str_ends_with($v, 'val') ||
    //             str_ends_with($v, 'sort') ||

    //             $v == 'basename' ||
    //             $v == 'dirname' ||
    //             $v == 'pathinfo' ||
    //             $v == 'implode' || $v == 'join' ||
    //             $v == 'explode' ||
    //             $v == 'trim' || $v == 'ltrim' || $v == 'rtrim' ||
    //             $v == 'similar_text' || $v == 'levenshtein' ||
    //             $v == 'ucfirst' || $v == 'ucwords' || $v == 'lcfirst' ||
    //             $v == 'strtolower' || $v == 'strtoupper' ||
    //             $v == 'chr' || $v == 'ord' ||
    //             $v == 'define'
    //             // $v == 'set_time_limit' ||
    //             // $v == 'session_start' ||
    //             // $v == 'call_user_func'
    //         ) {
    //             return false;
    //         }

    //         return true;
    //     });

    //     return $disabled_features;
    // }

    // function get_disabled_classes($scopes = []) {
    //     return [];
    // }
?>