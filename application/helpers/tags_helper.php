<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Function that add and edit tags based on passed arguments
 * @param  string $tags
 * @param  mixed $rel_id
 * @param  string $rel_type
 * @return boolean
 */
function handle_tags_save($tags, $rel_id, $rel_type)
{
    $CI =& get_instance();

    $affectedRows = 0;
    if ($tags == '') {
        $CI->db->where('rel_id', $rel_id);
        $CI->db->where('rel_type', $rel_type);
        $CI->db->delete('tbltags_in');
        if ($CI->db->affected_rows() > 0) {
            $affectedRows++;
        }
    } else {
        $tags_array = array();
        if (!is_array($tags)) {
            $tags = explode(',', $tags);
        }

        foreach ($tags as $tag) {
            $tag = trim($tag);
            if ($tag != '') {
                array_push($tags_array, $tag);
            }
        }

        // Check if there is removed tags
        $current_tags = get_tags_in($rel_id, $rel_type);

        foreach ($current_tags as $tag) {
            if (!in_array($tag, $tags_array)) {
                $tag = get_tag_by_name($tag);
                $CI->db->where('rel_id', $rel_id);
                $CI->db->where('rel_type', $rel_type);
                $CI->db->where('tag_id', $tag->id);
                $CI->db->delete('tbltags_in');
                if ($CI->db->affected_rows() > 0) {
                    $affectedRows++;
                }
            }
        }

        // Insert new ones
        $order = 1;
        foreach ($tags_array as $tag) {

             // Double quotes not allowed
            $tag = str_replace('"', '\'', $tag);

            $CI->db->where('name', $tag);
            $tag_row = $CI->db->get('tbltags')->row();
            if ($tag_row) {
                $tag_id = $tag_row->id;
            } else {
                $CI->db->insert('tbltags', array('name'=>$tag));
                $tag_id = $CI->db->insert_id();
                do_action('new_tag_created', $tag_id);
            }

            if (total_rows('tbltags_in', array('tag_id'=>$tag_id, 'rel_id'=>$rel_id, 'rel_type'=>$rel_type)) == 0) {
                $CI->db->insert(
                    'tbltags_in',
                    array(
                        'tag_id'=>$tag_id,
                        'rel_id'=>$rel_id,
                        'rel_type'=>$rel_type,
                        'tag_order'=>$order,
                        )
                );

                if ($CI->db->affected_rows() > 0) {
                    $affectedRows++;
                }
            }
            $order++;
        }
    }

    return ($affectedRows > 0 ? true : false);
}
/**
 * Get tag from db by name
 * @param  string $name
 * @return object
 */
function get_tag_by_name($name)
{
    $CI =& get_instance();
    $CI->db->where('name', $name);

    return $CI->db->get('tbltags')->row();
}
/**
 * Function that will return all tags used in the app
 * @return array
 */
function get_tags()
{
    $CI = &get_instance();

    $tags = $CI->object_cache->get('db-tags-array');

    if(!$tags && !is_array($tags)){
        $CI->db->order_by('name', 'ASC');
        $tags = $CI->db->get('tbltags')->result_array();
        $CI->object_cache->add('db-tags-array',$tags);
    }

    return $tags;
}
/**
 * Array of available tags without the keys
 * @return array
 */
function get_tags_clean()
{
    $tmp_tags = array();
    $tags = get_tags();
    foreach ($tags as $tag) {
        array_push($tmp_tags, $tag['name']);
    }
    $tags = $tmp_tags;

    return $tags;
}
/**
 * Get all tag ids
 * @return array
 */
function get_tags_ids()
{
    $tmp_tags = array();
    $tags = get_tags();
    foreach ($tags as $tag) {
        array_push($tmp_tags, $tag['id']);
    }
    $tags = $tmp_tags;

    return $tags;
}
/**
 * Function that will parse all the tags and return array with the names
 * @param  string $rel_id
 * @param  string $rel_type
 * @return array
 */
function get_tags_in($rel_id, $rel_type)
{
    $CI =& get_instance();
    $CI->db->where('rel_id', $rel_id);
    $CI->db->where('rel_type', $rel_type);
    $CI->db->order_by('tag_order', 'ASC');
    $tags = $CI->db->get('tbltags_in')->result_array();

    $tag_names = array();
    foreach ($tags as $tag) {
        $CI->db->where('id', $tag['tag_id']);
        $tag_row = $CI->db->get('tbltags')->row();
        if ($tag_row) {
            array_push($tag_names, $tag_row->name);
        }
    }

    return $tag_names;
}

/**
 * Coma separated tags for input
 * @param  array $tag_names
 * @return string
 */
function prep_tags_input($tag_names)
{
    $tag_names = array_filter($tag_names, function ($value) {
        return $value !== '';
    });

    return implode(',', $tag_names);
}
/**
 * Function will render tags as html version to show to the user
 * @param  string $tags
 * @return string
 */
function render_tags($tags)
{
    $tags_html = '';
    if (!is_array($tags)) {
        $tags = explode(',', $tags);
    }
    $tags = array_filter($tags, function ($value) {
        return $value !== '';
    });
    if (count($tags) > 0) {
        $CI = &get_instance();
        $tags_html .= '<div class="tags-labels">';
        $i = 0;
        $len = count($tags);
        foreach ($tags as $tag) {
            $tag_id = 0;
            $tag_row = $CI->object_cache->get('tag-id-by-name-'.$tag);
            if(!$tag_row){
                $CI->db->select('id')->where('name', $tag);
                $tag_row = $CI->db->get('tbltags')->row();
                if($tag_row){
                    $CI->object_cache->add('tag-id-by-name-'.$tag,$tag_row->id);
                }
            }

            if ($tag_row) {
                $tag_id = is_object($tag_row) ? $tag_row->id : $tag_row;
            }

            $tags_html .= '<span class="label label-tag tag-id-'.$tag_id.'"><span class="tag">'.$tag.'</span><span class="hide">'.($i != $len - 1 ? ', ' : '') .'</span></span>';
            $i++;
        }
        $tags_html .= '</div>';
    }

    return $tags_html;
}
