<?php
/*
 * For more information, please visit:
 * @link http://www.deluxeblogtips.com/meta-box/
 */


$prefix = '_ungrynerd_';

global $meta_boxes;

$meta_boxes = array();

$meta_boxes[] = array(
        'id'         => 'links_options',
        'title'      =>  __('Enlaces de portada'),
        'pages'      => array('page'), // Post type
        'context'    => 'normal',
        'priority'   => 'high',
        'show_names' => true, // Show field names on the left
        'fields'     => array(
            array(
                    'name' =>  __('Título principal'),
                    'id' => $prefix . 'main_title',
                    'type' => 'text',
                ),
            array(
                    'name' =>  __('Texto debajo del título'),
                    'id' => $prefix . 'subtitle',
                    'type' => 'wysiwyg',
                ),
            array(
                    'name' =>  __('Título del enlace'),
                    'id' => $prefix . 'link_title',
                    'type' => 'text',
                    'clone' => true,
                ),
            array(
                    'name' =>  __('Texto del enlace'),
                    'id' => $prefix . 'link_text',
                    'type' => 'textarea',
                    'clone' => true,
                ),
            array(
                    'name' =>  __('URL de enlace'),
                    'id' => $prefix . 'link_href',
                    'type' => 'text',
                    'clone' => true,
                ),

        ),
);

$meta_boxes[] = array(
        'id'         => 'general_options',
        'title'      =>  __('Portada'),
        'pages'      => array('post' ), // Post type
        'context'    => 'normal',
        'priority'   => 'high',
        'show_names' => true, // Show field names on the left
        'fields'     => array(
            array(
	                'name' =>  __('Destacar en portada'),
	                'id' => $prefix . 'featured',
	                'type' => 'checkbox',
	            ),
            array(
                    'name' =>  __('Destacar en seccion'),
                    'id' => $prefix . 'section_featured',
                    'type' => 'checkbox',
                ),
            array(
                    'name' =>  __('Destacar en etiqueta'),
                    'id' => $prefix . 'product_featured',
                    'type' => 'checkbox',
                )
        ),
    );
$meta_boxes[] = array(
        'id'         => 'catalogo_options',
        'title'      =>  __('Datos de catálogo'),
        'pages'      => array('post' ), // Post type
        'context'    => 'normal',
        'priority'   => 'high',
        'show_names' => true, // Show field names on the left
        'fields'     => array(
            array(
	                'name' =>  __('Fotografías del catálogo'),
	                'id' => $prefix . 'images',
	                'type' => 'plupload_image',
	            )
        ),
    );

function ungrynerd_register_meta_boxes()
{
	if ( !class_exists( 'RW_Meta_Box' ) )
		return;

	global $meta_boxes;
	foreach ( $meta_boxes as $meta_box )
	{
		new RW_Meta_Box( $meta_box );
	}
}
add_action( 'admin_init', 'ungrynerd_register_meta_boxes' );
