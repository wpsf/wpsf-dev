<?php
/**
 * Created by PhpStorm.
 * User: varun
 * Date: 01-03-2018
 * Time: 07:32 AM
 */

/**
 * Class ArrayFinder
 */
class ArrayFinder implements ArrayAccess, Countable, Iterator, Serializable {
    private $content       = [];
    private $position      = 0;
    private $pathSeparator = '.';

    /**
     * ArrayFinder constructor.
     *
     * @param array $content Content of the array
     */
    public function __construct(array $content = []) {
        $this->content = $content;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset) {
        if( strpos($offset, $this->pathSeparator) !== FALSE ) {
            $explodedPath  = explode($this->pathSeparator, $offset);
            $lastOffset    = array_pop($explodedPath);
            $offsetExists  = FALSE;
            $containerPath = implode($this->pathSeparator, $explodedPath);

            $this->callAtPath($containerPath, function($container) use ($lastOffset, &$offsetExists) {
                $offsetExists = isset($container[$lastOffset]);
            });

            return $offsetExists;
        } else {
            return isset($this->content[$offset]);
        }
    }

    /**
     * @param          $path
     * @param callable $callback
     * @param bool     $createPath
     * @param null     $currentOffset
     */
    private function callAtPath($path, callable $callback, $createPath = FALSE, &$currentOffset = NULL) {
        if( $currentOffset === NULL ) {
            $currentOffset = &$this->content;
            if( is_string($path) && $path == '' ) {
                $callback($currentOffset);
                return;
            }
        }

        $explodedPath = explode($this->pathSeparator, $path);
        $nextPath     = array_shift($explodedPath);

        if( ! isset($currentOffset[$nextPath]) ) {
            if( $createPath ) {
                $currentOffset[$nextPath] = [];
            } else {
                return;
            }
        }

        if( count($explodedPath) > 0 ) {
            $this->callAtPath(implode($this->pathSeparator, $explodedPath), $callback, $createPath, $currentOffset[$nextPath]);
        } else {
            $callback($currentOffset[$nextPath]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset) {
        return $this->get($offset);
    }

    /**
     * Return a value from the array corresponding to the path.
     * If the path is not set in the array, then $default is returned.
     *
     * ex:
     * $a = ['a' => ['b' => 'yeah']];
     * echo $this->get('a.b'); // yeah
     * echo $this->get('a.b.c', 'nope'); // nope
     *
     * @param string|int|null $path Path to the value. If null, return all the content.
     * @param mixed           $default Default value to return when path is not contained in the array.
     *
     * @return mixed|null Value on the array corresponding to the path, null if the key does not exist.
     */
    public function get($path = NULL, $default = NULL) {
        if( $path === NULL ) {
            return $this->content;
        }

        $value = $default;
        $this->callAtPath($path, function(&$offset) use (&$value) {
            $value = $offset;
        });

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value) {
        if( is_null($offset) ) {
            $this->content[] = $value;
        } else {
            $this->content[$offset] = $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset) {
        $path        = explode($this->pathSeparator, $offset);
        $pathToUnset = array_pop($path);

        $this->callAtPath(implode($this->pathSeparator, $path), function(&$offset) use (&$pathToUnset) {
            unset($offset[$pathToUnset]);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function count() {
        return count($this->content);
    }

    /**
     * {@inheritdoc}
     */
    public function current() {
        $keys = array_keys($this->content);
        return $this->content[$keys[$this->position]];
    }

    /**
     * {@inheritdoc}
     */
    public function next() {
        ++$this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function key() {
        $keys = array_keys($this->content);
        return $keys[$this->position];
    }

    /**
     * {@inheritdoc}
     */
    public function valid() {
        $keys = array_keys($this->content);
        return isset($keys[$this->position]);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind() {
        $this->position = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize() {
        return serialize($this->content);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($content) {
        $this->content = unserialize($content);
    }

    /**
     * Change the path separator of the array wrapper.
     *
     * By default, the separator is: .
     *
     * @param string $separator Separator to set.
     *
     * @return ArrayFinder Current instance.
     */
    public function changeSeparator($separator) {
        $this->pathSeparator = $separator;
        return $this;
    }

    /**
     * Insert a value to the array at the specified path.
     *
     * ex:
     * $this->set('a.b', 'yeah); // ['a' => ['b' => 'yeah']]
     *
     * @param string $path Path where the values will be insered.
     * @param mixed  $value Value ti insert.
     *
     * @return ArrayFinder Current instance.
     */
    public function set($path, $value) {
        $this->callAtPath($path, function(&$offset) use ($value) {
            $offset = $value;
        }, TRUE);

        return $this;
    }
}

/**
 * Class WPSFramework_Field
 */
class WPSFramework_Field {
    public    $current_path = array();
    protected $data         = array();
    protected $cache_elem   = array();
    protected $last_field   = '';

    /**
     * @param string $class
     * @return \WPSFramework_Field
     */
    public function wrap_class($class = '') {
        return $this->set_extra_attribute('wrap_class', $class);
    }

    /**
     * @param string $type
     * @param string $value
     * @param bool   $merge
     * @return $this
     */
    private function set_extra_attribute($type = '', $value = '', $merge = FALSE) {
        $this->cache_element();

        if( ! empty($this->cache_elem) ) {
            if( ! isset($this->cache_elem[$type]) ) {
                $this->cache_elem[$type] = $value;
            } else if( isset($this->cache_elem[$type]) === TRUE && is_array($this->cache_elem[$type]) === FALSE && $merge === TRUE ) {
                $this->cache_elem[$type] = $value;
            } else if( isset($this->cache_elem[$type]) === TRUE && is_array($this->cache_elem[$type]) === TRUE && $merge === TRUE ) {
                $this->cache_elem[$type] = array_merge($value, $this->cache_elem[$type]);
            }
        }

        return $this;
    }

    /**
     * cache_element
     */
    protected function cache_element() {
        if( empty($this->cache_elem) ) {
            $array_filter     = new ArrayFinder($this->data);
            $path             = $this->current_path;
            $path[]           = $this->last_field;
            $array            = $array_filter->get(implode('.', $path));
            $this->cache_elem = $array;
        }
    }

    /**
     * @return array
     */
    public function get() {
        $this->save_cache_element();
        return $this->data;
    }

    /**
     * save_cache_element
     */
    protected function save_cache_element() {
        if( ! empty($this->cache_elem) ) {
            $array_filter = new ArrayFinder($this->data);
            $path         = $this->current_path;
            $path[]       = $this->last_field;
            $array_filter->set(implode('.', $path), $this->cache_elem);
            $this->data       = $array_filter->get();
            $this->cache_elem = array();
        }
    }

    /**
     * @param string $key
     * @param string $value
     * @return \WPSFramework_Field
     */
    public function attribute($key = '', $value = '') {
        return $this->attributes(array( $key => $value ));
    }

    /**
     * @param $array
     * @return \WPSFramework_Field
     */
    private function attributes($array) {
        return $this->set_extra_attribute('attributes', $array, TRUE);
    }
}

/**
 * Class WPSFramework_Fields
 */
Class WPSFramework_Fields extends WPSFramework_Field {
    private $page_id    = NULL;
    private $section_id = NULL;

    /**
     * @param string $slug
     * @param string $title
     * @param string $icon
     * @return $this
     */
    public function page($slug = '', $title = '', $icon = '') {
        $page = array(
            'name'  => empty($slug) ? rand(1, 100) : $slug,
            'title' => $title,
            'icon'  => $icon,
        );

        $this->data[$page['name']] = $page;
        $this->page_id             = $page['name'];
        $this->section_id          = NULL;
        $this->current_path        = array( $page['name'] );
        return $this;
    }

    /**
     * @param string $slug
     * @param string $title
     * @param string $icon
     * @return $this
     */
    public function section($slug = '', $title = '', $icon = '') {
        $section = array(
            'name'   => empty($slug) ? rand(1, 100) : $slug,
            'title'  => $title,
            'icon'   => $icon,
            'fields' => array(),
        );

        if( ! is_null($this->page_id) || ! empty($this->page_id) ) {
            if( ! isset($this->data[$this->page_id]['sections']) ) {
                $this->data[$this->page_id]['sections'] = array();
            }
            $this->data[$this->page_id]['sections'][$section['name']] = $section;
            $this->add_path(array( $this->page_id, 'sections', $section['name'], 'fields' ), TRUE);
        } else {
            $this->data[$section['name']] = $section;
            $this->add_path(array( $section['name'], 'fields' ), TRUE);
        }

        $this->section_id = $section['name'];
        return $this;
    }

    /**
     * @param string $data
     * @param bool   $force
     */
    protected function add_path($data = '', $force = FALSE) {
        if( $force ) {
            $this->current_path = $data;
        } else {
            if( is_array($data) ) {
                $this->current_path = array_merge($this->current_path, $data);
            } else {
                $this->current_path[] = $data;
            }
        }
    }

    /**
     * @param string $id
     * @param string $title
     * @param string $button_title
     * @param string $accordion_title
     * @param array  $defaults
     * @return \WPSFramework_Fields
     */
    public function group($id = '', $title = '', $button_title = 'Add', $accordion_title = 'Add New Field', $defaults = array()) {
        return $this->__fields('group', array(
            'id'              => $id,
            'title'           => $title,
            'button_title'    => $button_title,
            'accordion_title' => $accordion_title,
            'fields'          => array(),
            'default'         => $defaults,
        ));
    }

    /**
     * @param string $type
     * @param        $data
     * @return $this
     */
    private function __fields($type = '', $data) {
        $this->field($type, '', '', '', $data);
        $this->add_path(array( $data['id'], 'fields' ));
        return $this;
    }

    /**
     * @param string $type
     * @param string $id
     * @param string $title
     * @param string $defaults
     * @param array  $fields
     * @return \WPSFramework_Fields
     * @uses icon
     *
     */
    public function field($type = '', $id = '', $title = '', $defaults = '', $fields = array()) {
        $fields = wp_parse_args($fields, array(
            'id'      => $id,
            'type'    => $type,
            'title'   => $title,
            'default' => $defaults,
        ));
        return $this->add_field($fields);
    }

    /**
     * @param array $field
     * @return $this
     */
    public function add_field($field = array()) {
        $this->save_cache_element();
        if( ( ! is_null($this->page_id) || ! empty($this->page_id) ) && ( is_null($this->section_id) || empty($this->section_id) ) ) {
            if( ! isset($this->data[$this->page_id]['fields']) ) {
                $this->data[$this->page_id]['fields'] = array();
                $this->add_path('fields');
            }
        }


        $array_filter        = new ArrayFinder($this->data);
        $array               = $array_filter->get(implode('.', $this->current_path));
        $array[$field['id']] = $field;
        $array_filter->set(implode('.', $this->current_path), $array);
        $this->data       = $array_filter->get();
        $this->last_field = $field['id'];
        return $this;
    }

    /**
     * @return \WPSFramework_Fields
     */
    public function end_group() {
        return $this->splice(-2);
    }

    /**
     * @param int $offset
     * @return $this
     */
    private function splice($offset = -1) {
        array_splice($this->current_path, $offset);
        return $this;
    }

    /**
     * @param $name
     * @param $arguments
     * @return $this
     */
    public function __call($name, $arguments) {
        if( in_array($name, array( 'icon', 'background', 'color_picker', 'typography' )) ) {
            $arguments = array_merge(array( $name ), $arguments);
            call_user_func_array(array( &$this, 'field' ), $arguments);
            return $this;
        } else if( in_array($name, array( 'image_select' )) ) {
            $arguments = array_merge(array( $name ), $arguments);
            call_user_func_array(array( &$this, 'checkbox_radio' ), $arguments);
            return $this;
        }
        if( in_array($name, array( 'content', 'heading', 'sub_heading' )) ) {
            $arguments = array_merge(array( $name ), $arguments);
            call_user_func_array(array( &$this, '__content' ), $arguments);
            return $this;
        }
    }

    public function __content($type = 'content', $content = '', $title = '', $meta = array()) {
        return $this->field($type, '', $title, '', array_merge($meta, array( 'content' => $content )));
    }

    /**
     * Set Text Field
     * @param string $id
     * @param string $title
     * @param string $default
     * @param array  $meta
     * @return \WPSFramework_Fields
     */
    public function text($id = '', $title = '', $default = '', $meta = array()) {
        return $this->field('text', $id, $title, $default, $meta);
    }

    /**
     * @param string $id
     * @param string $title
     * @param string $default
     * @param array  $meta
     * @return \WPSFramework_Fields
     */
    public function icon($id = '', $title = '', $default = '', $add_label = 'Add Icon', $remove_label = 'Remove Icon', $meta = array()) {
        if( ! empty($add_label) ) {
            $meta['add_label'] = $add_label;
        }

        if( ! empty($remove_label) ) {
            $meta['remove_label'] = $remove_label;
        }
        return $this->field('icon', $id, $title, $default, $meta);
    }

    /**
     * @param string $id
     * @param string $title
     * @param string $default
     * @param string $min
     * @param string $max
     * @param array  $meta
     */
    public function number($id = '', $title = '', $default = '', $min = '', $max = '', $meta = array()) {
        $this->field('number', $id, $title, $default, $meta);
        if( ! empty($max) ) {
            $this->attribute('max', $max);
        }

        if( ! empty($min) ) {
            $this->attribute('min', $min);
        }

        return $this;
    }

    /**
     * Set Textare Field
     * @param string $id
     * @param string $title
     * @param string $default
     * @param bool   $shortcode
     * @param array  $meta
     * @return \WPSFramework_Fields
     */
    public function textarea($id = '', $title = '', $default = '', $shortcode = FALSE, $meta = array()) {
        if( $shortcode !== FALSE ) {
            $meta['shortcode'] = $shortcode;
        }
        return $this->field('textarea', $id, $title, $default, $meta);
    }

    /**
     * @param string $id
     * @param string $title
     * @param string $option
     * @param string $default
     * @param array  $query_args
     * @param array  $meta
     * @return \WPSFramework_Fields
     */
    public function checkbox($id = '', $title = '', $option = '', $default = '', $query_args = array(), $meta = array()) {
        return $this->checkbox_radio('checkbox', $id, $title, $option, $default, $query_args, $meta);
    }

    /**
     * @param string $type
     * @param string $id
     * @param string $title
     * @param string $options
     * @param string $default
     * @param array  $query_args
     * @param array  $meta
     * @return \WPSFramework_Fields
     */
    public function checkbox_radio($type = 'checkbox', $id = '', $title = '', $options = '', $default = '', $query_args = array(), $meta = array()) {
        if( is_array($options) ) {
            $meta['options'] = $options;
        } else {
            $meta['label'] = $options;
        }
        $meta['query_args'] = $query_args;

        return $this->field($type, $id, $title, $default, $meta);
    }

    /**
     * @param string $id
     * @param string $title
     * @param string $option
     * @param string $default
     * @param array  $query_args
     * @param array  $meta
     * @return \WPSFramework_Fields
     */
    public function radio($id = '', $title = '', $option = '', $default = '', $query_args = array(), $meta = array()) {
        return $this->checkbox_radio('radio', $id, $title, $option, $default, $query_args, $meta);
    }

    /**
     * @param string $id
     * @param string $title
     * @param string $option
     * @param bool   $multiple
     * @param string $default
     * @param array  $query_args
     * @param array  $meta
     * @return \WPSFramework_Fields
     */
    public function select($id = '', $title = '', $option = '', $multiple = FALSE, $default = '', $query_args = array(), $meta = array()) {
        if( $multiple === TRUE ) {
            $meta['multiple'] = $multiple;
        }

        return $this->checkbox_radio('select', $id, $title, $option, $default, $query_args, $meta);
    }

    /**
     * @param string $id
     * @param string $title
     * @param string $label
     * @param string $on_label
     * @param string $off_label
     * @param string $default
     * @param array  $meta
     * @return \WPSFramework_Fields
     */
    public function switcher($id = '', $title = '', $label = '', $on_label = 'On', $off_label = 'off', $default = '', $meta = array()) {
        $meta['label']     = $label;
        $meta['on_label']  = $on_label;
        $meta['off_label'] = $off_label;
        return $this->field('switcher', $id, $title, $default, $meta);
    }

    /**
     * @param string $id
     * @param string $title
     * @param array  $settings
     * @param array  $meta
     * @return \WPSFramework_Fields
     */
    public function upload($id = '', $title = '', $settings = array(), $meta = array()) {
        if( ! empty($settings) ) {
            $meta['settings'] = $settings;
        }
        return $this->field('upload', $id, $title, '', $meta);
    }

    /**
     * @param string $id
     * @param string $title
     * @param array  $settings
     * @param array  $meta
     * @return \WPSFramework_Fields
     */
    public function rich_text($id = '', $title = '', $settings = array(), $meta = array()) {
        return $this->wysiwyg($id, $title, $settings, $meta);
    }

    /**
     * @param string $id
     * @param string $title
     * @param array  $settings
     * @param array  $meta
     * @return \WPSFramework_Fields
     */
    public function wysiwyg($id = '', $title = '', $settings = array(), $meta = array()) {
        if(!empty($settings)){
            $meta['settings'] = $meta;
        }
        return $this->field('wysiwyg', $id, $title, '', $meta);
    }

}