<?php
defined('ABSPATH') ?: exit();

/**
 * Register admin/options page
 */
add_action('acf/init', function () {

    if (function_exists('acf_add_options_page')) :
        // Register options page.
        $option_page = acf_add_options_page(array(
            'page_title' => __('CISO Threat Globe Text Settings'),
            'menu_title' => __('CISO Threat Globe'),
            'menu_slug'  => 'ciso-tg-settings',
            'capability' => 'edit_posts',
            'position'   => 20,
            'icon_url'   => 'dashicons-admin-site',
            'redirect'   => false,
            'autoload'   => true
        ));
    endif;
});

/**
 * Add fields to CISO settgings page
 */
add_action('acf/include_fields', function () {

    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group(array(
        'key'    => 'group_646b74dea32ec',
        'title'  => 'CISO Globe Popup Text',
        'fields' => array(
            array(
                'key'               => 'field_646b76d7fb7fd',
                'label'             => 'Country data set',
                'name'              => 'country_data_set',
                'aria-label'        => '',
                'type'              => 'repeater',
                'instructions'      => '<b>Holds per country data to be displayed/overlaid on threat globe.<br> Use shortcode [ciso_threat_globe] to display the globe on the page of your choice.</b>',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'layout'        => 'block',
                'pagination'    => 0,
                'min'           => 0,
                'max'           => 0,
                'collapsed'     => '',
                'button_label'  => '+ Add Data Set',
                'rows_per_page' => 20,
                'sub_fields'    => array(
                    array(
                        'key'               => 'field_646b74defb7f9',
                        'label'             => 'Select country',
                        'name'              => 'select_country',
                        'aria-label'        => '',
                        'type'              => 'select',
                        'instructions'      => 'Select country from the list of countries',
                        'required'          => 1,
                        'conditional_logic' => 0,
                        'wrapper'           => array(
                            'width' => '33',
                            'class' => '',
                            'id'    => '',
                        ),
                        'choices' => array(
                            'Argentina'                => 'Argentina',
                            'Australia'                => 'Australia',
                            'Austria'                  => 'Austria',
                            'Bahrain'                  => 'Bahrain',
                            'Belgium'                  => 'Belgium',
                            'Brazil'                   => 'Brazil',
                            'Canada'                   => 'Canada',
                            'Chile'                    => 'Chile',
                            'China'                    => 'China',
                            'Columbia'                 => 'Columbia',
                            'Czech Republic'           => 'Czech Republic',
                            'Denmark'                  => 'Denmark',
                            'Finland'                  => 'Finland',
                            'France'                   => 'France',
                            'Germany'                  => 'Germany',
                            'Hungary'                  => 'Hungary',
                            'Ireland'                  => 'Ireland',
                            'Italy'                    => 'Italy',
                            'Japan'                    => 'Japan',
                            'Luxembourg'               => 'Luxembourg',
                            'Mexico'                   => 'Mexico',
                            'Morocco'                  => 'Morocco',
                            'Netherlands'              => 'Netherlands',
                            'New Zealand'              => 'New Zealand',
                            'Norway'                   => 'Norway',
                            'Oman'                     => 'Oman',
                            'Peru'                     => 'Peru',
                            'Poland'                   => 'Poland',
                            'Portugal'                 => 'Portugal',
                            'Puerto Rico'              => 'Puerto Rico',
                            'Qatar'                    => 'Qatar',
                            'Romania'                  => 'Romania',
                            'Singapore'                => 'Singapore',
                            'Slovakia'                 => 'Slovakia',
                            'South Africa'             => 'South Africa',
                            'South Korea'              => 'South Korea',
                            'Spain'                    => 'Spain',
                            'Sweden'                   => 'Sweden',
                            'Thailand'                 => 'Thailand',
                            'United Arab Emirates'     => 'United Arab Emirates',
                            'United Kingdom'           => 'United Kingdom',
                            'United States of America' => 'United States of America'
                        ),
                        'default_value'   => false,
                        'return_format'   => 'value',
                        'multiple'        => 0,
                        'allow_null'      => 0,
                        'ui'              => 1,
                        'ajax'            => 1,
                        'placeholder'     => '',
                        'parent_repeater' => 'field_646b76d7fb7fd',
                    ),
                    array(
                        'key'               => 'field_646b769dfb7fb',
                        'label'             => 'Threat rating',
                        'name'              => 'threat_rating',
                        'aria-label'        => '',
                        'type'              => 'number',
                        'instructions'      => 'Theat rating for this country',
                        'required'          => 1,
                        'conditional_logic' => 0,
                        'wrapper'           => array(
                            'width' => '33',
                            'class' => '',
                            'id'    => '',
                        ),
                        'default_value'   => '',
                        'min'             => 1,
                        'max'             => '',
                        'placeholder'     => '',
                        'step'            => 1,
                        'prepend'         => '',
                        'append'          => '',
                        'parent_repeater' => 'field_646b76d7fb7fd',
                    ),
                    array(
                        'key'               => 'field_646b76b7fb7fc',
                        'label'             => 'Impact rating',
                        'name'              => 'impact_rating',
                        'aria-label'        => '',
                        'type'              => 'number',
                        'instructions'      => 'Impact rating for this country',
                        'required'          => 1,
                        'conditional_logic' => 0,
                        'wrapper'           => array(
                            'width' => '33',
                            'class' => '',
                            'id'    => '',
                        ),
                        'default_value'   => '',
                        'min'             => 1,
                        'max'             => '',
                        'placeholder'     => '',
                        'step'            => 1,
                        'prepend'         => '',
                        'append'          => '',
                        'parent_repeater' => 'field_646b76d7fb7fd',
                    ),
                    array(
                        'key'               => 'field_646b762afb7fa',
                        'label'             => 'Privacy Law',
                        'name'              => 'privacy_law',
                        'aria-label'        => '',
                        'type'              => 'textarea',
                        'instructions'      => 'Add your privacy law text here. Maximum allowed characters is 350.',
                        'required'          => 1,
                        'conditional_logic' => 0,
                        'wrapper'           => array(
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ),
                        'default_value'   => '',
                        'maxlength'       => 350,
                        'rows'            => 4,
                        'placeholder'     => '',
                        'new_lines'       => '',
                        'parent_repeater' => 'field_646b76d7fb7fd',
                    ),
                ),
            ),
        ),
        'location' => array(
            array(
                array(
                    'param'    => 'options_page',
                    'operator' => '==',
                    'value'    => 'ciso-tg-settings',
                ),
            ),
        ),
        'menu_order'            => 0,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen'        => '',
        'active'                => true,
        'description'           => '',
        'show_in_rest'          => 0,
    ));
});
