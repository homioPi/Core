<?php 
    namespace HomioPi\Widgets;

    use HomioPi\Interfaces\defaultInterface;

    class Widget extends defaultInterface {
        protected $extension_id;
        protected $extension_dir;
        protected $id;

        public function __construct($extension_id, $id) {
            $this->extension_id  = $extension_id;
            $this->extension_dir = DIR_EXTENSIONS."/{$extension_id}";
            $this->id            = $id;
        }

        public function getProperties() {
            $extension = new \HomioPi\Extensions\Extension($this->extension_id);

            $properties = $extension->getProperties()['features']['dashboard/widgets'][$this->id] ?? [];

            return array_merge([
                'extension_id' => $this->extension_id,
                'id' => $this->id
            ], $properties);
        }

        public function getHTML() {
            $extension = new \HomioPi\Extensions\Extension($this->extension_id);
            $html      = $extension->loadFeatureComponent('dashboard/widgets', $this->id, 'main');

            return $html;
        }

        public function getCSS() {
            $extension = new \HomioPi\Extensions\Extension($this->extension_id);
            $css       = $extension->loadFeatureComponent('dashboard/widgets', $this->id, 'widget.css');

            // ================================= //
            //    Limit styles to this widget    //
            // ================================= //

            // Remove all whitespaces that aren't between quotes
            $css = preg_replace('/[^\S ]+/', '', $css);

            // Split styles in to array (each item is a css rule)
            $css = explode('}', $css);

            // Prepend widget selector to css rule
            foreach ($css as &$rule) {
                $rule = $rule . '}';

                if(!str_contains($rule, '{') || str_starts_with($rule, '@')) {
                    continue;
                }

                $rule = ".dashboard-widget[data-widget-id=\"{$this->extension_id}_{$this->id}\"] {$rule}";
            }

            $css = implode('', $css);

            return $css;
        }

        public function getJS() {
            $extension = new \HomioPi\Extensions\Extension($this->extension_id);
            $js        = $extension->loadFeatureComponent('dashboard/widgets', $this->id, 'widget.js');
            
            // Inject javascript which will register the widget
            $js = "
                HomioPi_assign('data.dashboard.widgets.{$this->extension_id}_{$this->id}',
                    $js
                );
            ";

            return $js;
        }

        public function translate($key, $replacements = null, $fallback = null) {
            $extension = new \HomioPi\Extensions\Extension($this->extension_id);
            return $extension->translate("features.dashboard/widgets.{$this->id}.{$key}", $replacements, $fallback);
        }
    }
?>