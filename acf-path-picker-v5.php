<?php

class acf_field_path_picker extends acf_field {


    /*
    *  __construct
    *
    *  This function will setup the field type data
    *
    *  @type    function
    *  @date    5/03/2014
    *  @since   5.0.0
    *
    *  @param   n/a
    *  @return  n/a
    */

    function __construct() {

        /*
        *  name (string) Single word, no spaces. Underscores allowed
        */

        $this->name = 'acf_path_picker';


        /*
        *  label (string) Multiple words, can include spaces, visible when selecting a field type
        */

        $this->label = __('File Path Picker', 'acf-path-picker');


        /*
        *  category (string) basic | content | choice | relational | jquery | layout | CUSTOM GROUP NAME
        */

        $this->category = 'layout';


        /*
        *  defaults (array) Array of default settings which are merged into the field object. These are used later in settings
        */

        $this->defaults = array(
                'path_slug'         => 'TEMPLATEPATH',
                'path_append'       => '',
                'selected_file'     => '',
                'allowed_file_types' => 'php twig html',
                'pretty_option_values' => 1,
                'recursive_search'  => 0,
            );


        /*
        *  l10n (array) Array of strings that are used in JavaScript. This allows JS strings to be translated in PHP and loaded via:
        *  var message = acf._e('fs-picker', 'error');
        */

        $this->l10n = array(
            'error' => __('Error!', 'acf-path-picker'),
        );


        // do not delete!
        parent::__construct();

    }


    /*
    *  render_field_settings()
    *
    *  Create extra settings for your field. These are visible when editing a field
    *
    *  @type    action
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   $field (array) the $field being edited
    *  @return  n/a
    */

    function render_field_settings( $field ) {

        /*
        *  acf_render_field_setting
        *
        *  This function will create a setting for your field. Simply pass the $field parameter and an array of field settings.
        *  The array of settings does not require a `value` or `prefix`; These settings are found from the $field array.
        *
        *  More than one setting can be added by copy/paste the above code.
        *  Please note that you must also have a matching $defaults value for the field name (font_size)
        */

        $tmpl_dir = get_template_directory();

        $default_choices = array(
            'TEMPLATEPATH'  => __('WP Theme path - '.$tmpl_dir, 'acf-path-picker'),
            'ABSPATH'       => __('ABSPATH Constant - '.ABSPATH,     'acf-path-picker'),
            'ROOTPATH'      => __('File System Root - /',               'acf-path-picker')
        );

        $path_choices = apply_filters('acfpp_custom_path_options', $default_choices);

        acf_render_field_setting( $field, array(
            'label'         => __('Relative Path',      'acf-path-picker'),
            'instructions'  => __('Specify which directory to search for files', 'acf-path-picker'),
            'type'          => 'select',
            'name'          => 'path_slug',
            'choices'       => $path_choices
        ));

        acf_render_field_setting( $field, array(
            'label'         => __('Append to Path','acf-path-picker'),
            'instructions'  => __('If your partials directory is in a subdirectory of your theme, specify <code>WP Theme</code> above, then add the subdirectory name here.','acf-path-picker'),
            'type'          => 'text',
            'name'          => 'path_append'
        ));

        acf_render_field_setting( $field, array(
            'label'         => __('Allowed File types','acf-path-picker'),
            'instructions'  => __('Specify allowed file types to show in dropdown. Separate by spaces. No dots or commas. Defaults: <code>' . $this->defaults['allowed_file_types'] . '</code>' ,'acf-path-picker'),
            'type'          => 'text',
            'name'          => 'allowed_file_types'
        ));

        // acf_render_field_setting( $field, array(
        //     'label'         => __('Recursive Search?','acf-path-picker'),
        //     'instructions'  => __('Recursively search through child directories for files?','acf-path-picker'),
        //     'type'          => 'true_false',
        //     'ui'            => $this->defaults['recursive_search'],
        //     'name'          => 'recursive_search'
        // ));

        acf_render_field_setting( $field, array(
            'label'         => __('Pretty Option Values','acf-path-picker'),
            'instructions'  => __('Remove file extension from file name. Convert dashes & underscores to spaces. Use Title Case For Text.','acf-path-picker'),
            'type'          => 'true_false',
            'ui'            => $this->defaults['pretty_option_values'],
            'name'          => 'pretty_option_values'
        ));

    }



    /*
    *  render_field()
    *
    *  Create the HTML interface for your field
    *
    *  @param   $field (array) the $field being rendered
    *
    *  @type    action
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   $field (array) the $field being edited
    *  @return  n/a
    */

    function render_field( $field ) {
        /*
        *  Review the data of $field.
        *  This will show what data is available
        */

        $is_pretty = (bool)$field['pretty_option_values'];
        // $is_recursive = (bool)$field['recursive_search'];


        $path = $this->_slug_to_path($field['path_slug'], $field['path_append']);

        $path = rtrim($path, DIRECTORY_SEPARATOR); // I don't wanna deal with this.
        $path .= DIRECTORY_SEPARATOR . trim($field['selected_file'], DIRECTORY_SEPARATOR);

        if (!$path && !is_dir($path))
        {
            echo "<strong>Path is incorrect or cannot read directory";
        }

        $allowed_types = strtolower($field['allowed_file_types']);
        $allowed_types = str_replace(array(',', '.'), ' ', $allowed_types);
        $allowed_types = preg_replace('/\s+/', ' ', $allowed_types);
        $allowed_types = explode(' ', $allowed_types);

        // Set up directory iterator
        // Recursive
        // if ($is_recursive){
        //     $iter = new RecursiveIteratorIterator(
        //         new RecursiveDirectoryIterator($path,
        //                 RecursiveDirectoryIterator::SKIP_DOTS),
        //         RecursiveIteratorIterator::SELF_FIRST,
        //         RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
        //     );
        // }
        // else { }
        $iter = new DirectoryIterator($path);

        if (iterator_count($iter) == 0) {
            $output = "<strong>No files found</strong> in path $path";
        }
        else
        {
            $option_html = "\n<option value=\"%s\" %s>%s</option>";
            $output = sprintf('<select name="%s">', esc_attr($field['name']));

            foreach ($iter as $dir) {
                // If it's a file (not directory)
                if (!$dir->isDir()) {

                    // Skip file if not allowed extension
                    $ext = pathinfo($dir->getFileName(), PATHINFO_EXTENSION);
                    if (!in_array($ext, $allowed_types)) continue;

                    $filename = esc_attr($dir->getFileName());
                    $current = esc_attr($field['value']);

                    $display_name = $filename;
                    if ($is_pretty) {
                        $display_name = pathinfo($filename,PATHINFO_FILENAME);
                        $display_name = str_replace(array('-', '_'), ' ', $display_name);
                        $display_name = preg_replace('/\s+/', ' ', $display_name);
                        $display_name = ucwords($display_name);
                    }

                    $output .= sprintf($option_html,
                                    $filename,
                                    ($filename === $current) ? ' selected' : '',
                                    $display_name
                                );
                }
            }
            $output .= "\n</select>\n";

            $output .= "<small>Searching for files in path: $path</small>\n";
        }

        echo $output;

    }


    function _slug_to_path( $slug, $path_append = false )
    {
        $default['TEMPLATEPATH']  = trim(get_template_directory());
        $default['ABSPATH']       = trim(ABSPATH);
        $default['ROOTPATH']      = '/';

        $all = apply_filters('acfpp_custom_paths', $default );

        if (array_key_exists($slug, $all)) {
            $path = $all[$slug];
            if ($path_append) {
                $path .= DIRECTORY_SEPARATOR . trim($path_append,DIRECTORY_SEPARATOR);
            }
            return $path;
        }
        return false;
    }

    /*
    *  input_admin_enqueue_scripts()
    *
    *  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
    *  Use this action to add CSS + JavaScript to assist your render_field() action.
    *
    *  @type    action (admin_enqueue_scripts)
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   n/a
    *  @return  n/a
    */

    /*

    function input_admin_enqueue_scripts() {

        $dir = plugin_dir_url( __FILE__ );


        // register & include JS
        wp_register_script( 'acf-input-fs-picker', "{$dir}js/input.js" );
        wp_enqueue_script('acf-input-fs-picker');


        // register & include CSS
        wp_register_style( 'acf-input-fs-picker', "{$dir}css/input.css" );
        wp_enqueue_style('acf-input-fs-picker');


    }

    */


    /*
    *  input_admin_head()
    *
    *  This action is called in the admin_head action on the edit screen where your field is created.
    *  Use this action to add CSS and JavaScript to assist your render_field() action.
    *
    *  @type    action (admin_head)
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   n/a
    *  @return  n/a
    */

    /*

    function input_admin_head() {



    }

    */


    /*
    *  input_form_data()
    *
    *  This function is called once on the 'input' page between the head and footer
    *  There are 2 situations where ACF did not load during the 'acf/input_admin_enqueue_scripts' and
    *  'acf/input_admin_head' actions because ACF did not know it was going to be used. These situations are
    *  seen on comments / user edit forms on the front end. This function will always be called, and includes
    *  $args that related to the current screen such as $args['post_id']
    *
    *  @type    function
    *  @date    6/03/2014
    *  @since   5.0.0
    *
    *  @param   $args (array)
    *  @return  n/a
    */

    function input_form_data( $args ) {

    }



    /*
    *  input_admin_footer()
    *
    *  This action is called in the admin_footer action on the edit screen where your field is created.
    *  Use this action to add CSS and JavaScript to assist your render_field() action.
    *
    *  @type    action (admin_footer)
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   n/a
    *  @return  n/a
    */

    function input_admin_footer() {





    }


    /*
    *  field_group_admin_enqueue_scripts()
    *
    *  This action is called in the admin_enqueue_scripts action on the edit screen where your field is edited.
    *  Use this action to add CSS + JavaScript to assist your render_field_options() action.
    *
    *  @type    action (admin_enqueue_scripts)
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   n/a
    *  @return  n/a
    */

    /*

    function field_group_admin_enqueue_scripts() {

    }

    */


    /*
    *  field_group_admin_head()
    *
    *  This action is called in the admin_head action on the edit screen where your field is edited.
    *  Use this action to add CSS and JavaScript to assist your render_field_options() action.
    *
    *  @type    action (admin_head)
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   n/a
    *  @return  n/a
    */

    /*

    function field_group_admin_head() {

    }

    */


    /*
    *  load_value()
    *
    *  This filter is applied to the $value after it is loaded from the db
    *
    *  @type    filter
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   $value (mixed) the value found in the database
    *  @param   $post_id (mixed) the $post_id from which the value was loaded
    *  @param   $field (array) the field array holding all the field options
    *  @return  $value
    */


    function load_value( $value, $post_id, $field ) {

        // OVERRIDE THIS
        //$post = get_post( $post_id, OBJECT );


        return $value;

    }



    /*
    *  update_value()
    *
    *  This filter is applied to the $value before it is saved in the db
    *
    *  @type    filter
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   $value (mixed) the value found in the database
    *  @param   $post_id (mixed) the $post_id from which the value was loaded
    *  @param   $field (array) the field array holding all the field options
    *  @return  $value
    */

    /*

    function update_value( $value, $post_id, $field ) {

        return $value;

    }

    */


    /*
    *  format_value()
    *
    *  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
    *
    *  @type    filter
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   $value (string) the value which was loaded from the database
    *  @param   $post_id (mixed) the $post_id from which the value was loaded
    *  @param   $field (array) the field array holding all the field options
    *
    *  @return  $value (mixed) the modified value
    */
    function format_value($value, $post_id, $field)
    {

        // bail early if no value
        if(empty($value)) return $value;

        $path = $this->_slug_to_path($field['path_slug'], $field['path_append']);
        $filename = $value;
        $value = [
            'path_slug'     => $field['path_slug'],
            'path_append'   => $field['path_append'],
            'path'          => $path,
            'filename'      => $filename,
            'full_path'     => $path . DIRECTORY_SEPARATOR . $filename
        ];

        // return
        return $value;
    }




    /*
    *  validate_value()
    *
    *  This filter is used to perform validation on the value prior to saving.
    *  All values are validated regardless of the field's required setting. This allows you to validate and return
    *  messages to the user if the value is not correct
    *
    *  @type    filter
    *  @date    11/02/2014
    *  @since   5.0.0
    *
    *  @param   $valid (boolean) validation status based on the value and the field's required setting
    *  @param   $value (mixed) the $_POST value
    *  @param   $field (array) the field array holding all the field options
    *  @param   $input (string) the corresponding input name for $_POST value
    *  @return  $valid
    */

    /*

    function validate_value( $valid, $value, $field, $input ){

        // Basic usage
        if( $value < $field['custom_minimum_setting'] )
        {
            $valid = false;
        }


        // Advanced usage
        if( $value < $field['custom_minimum_setting'] )
        {
            $valid = __('The value is too little!','acf-fs-path-picker'),
        }


        // return
        return $valid;

    }

    */


    /*
    *  delete_value()
    *
    *  This action is fired after a value has been deleted from the db.
    *  Please note that saving a blank value is treated as an update, not a delete
    *
    *  @type    action
    *  @date    6/03/2014
    *  @since   5.0.0
    *
    *  @param   $post_id (mixed) the $post_id from which the value was deleted
    *  @param   $key (string) the $meta_key which the value was deleted
    *  @return  n/a
    */

    /*

    function delete_value( $post_id, $key ) {



    }

    */


    /*
    *  load_field()
    *
    *  This filter is applied to the $field after it is loaded from the database
    *
    *  @type    filter
    *  @date    23/01/2013
    *  @since   3.6.0
    *
    *  @param   $field (array) the field array holding all the field options
    *  @return  $field
    */

    /*

    function load_field( $field ) {

        return $field;

    }

    */


    /*
    *  update_field()
    *
    *  This filter is applied to the $field before it is saved to the database
    *
    *  @type    filter
    *  @date    23/01/2013
    *  @since   3.6.0
    *
    *  @param   $field (array) the field array holding all the field options
    *  @return  $field
    */

    /*

    function update_field( $field ) {

        return $field;

    }

    */


    /*
    *  delete_field()
    *
    *  This action is fired after a field is deleted from the database
    *
    *  @type    action
    *  @date    11/02/2014
    *  @since   5.0.0
    *
    *  @param   $field (array) the field array holding all the field options
    *  @return  n/a
    */

    /*

    function delete_field( $field ) {



    }

    */


}


// create field
new acf_field_path_picker();
