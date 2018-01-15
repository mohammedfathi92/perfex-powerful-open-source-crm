<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Newsfeed extends Admin_controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('newsfeed_model');
    }
    /* Init newsfeed in homepage */
    public function load_newsfeed()
    {
        $__post_id = '';
        if ($this->input->post('postid')) {
            $__post_id = $this->input->post('postid');
        }

        $posts    = $this->newsfeed_model->load_newsfeed($this->input->post('page'), $__post_id);
        $response = '';

        $this->load->model('departments_model');
        $staff_deparments = $this->departments_model->get_staff_departments(get_staff_user_id(), true);
        // we do not need to add pinned post additionally when refreshing only 1 post
        if (!$this->input->post('postid') && ($this->input->post('page') == 0)) {
            $pinned_posts = $this->newsfeed_model->get_pinned_posts();
            foreach ($pinned_posts as $pinned_post) {
                array_unshift($posts, $pinned_post);
            }
        }
        foreach ($posts as $post) {
            $visible_departments = '';
            $not_visible         = false;
            $visibility          = explode(':', $post['visibility']);
            if ($visibility[0] != 'all') {
                for ($i = 0; $i < count($visibility); $i++) {
                    if (!in_array($visibility[$i], $staff_deparments)) {
                        // Allow admin to view all posts
                        if (!is_admin()) {
                            if ($post['creator'] != get_staff_user_id()) {
                                $not_visible = true;
                            }
                        }
                    }
                    $visible_departments .= $this->departments_model->get($visibility[$i])->name . ', ';
                }
            }
            if ($not_visible == true) {
                continue;
            }

            $pinned_class = '';

            if ($post['pinned'] == 1) {
                $pinned_class = ' pinned';
            }

            $response .= '<div class="panel_s newsfeed_post' . $pinned_class . '" data-main-postid="' . $post['postid'] . '">';
            $response .= '<div class="panel-body post-content">';
            $response .= '<div class="media">';
            $response .= '<div class="media-left">';
            $response .= '<a href="' . admin_url('profile/' . $post['creator']) . '">' . staff_profile_image($post['creator'], array(
                'staff-profile-image-small',
                'no-radius'
            )) . '</a>';

            $response .= '</div>';
            $response .= '<div class="media-body">';
            $response .= '<p class="media-heading no-mbot"><a href="' . admin_url('profile/' . $post['creator']) . '">' . get_staff_full_name($post['creator']) . '</a></p>';
            $response .= '<small class="post-time-ago">' . time_ago($post['datecreated']) . '</small>';
            if ($post['creator'] == get_staff_user_id() || is_admin()) {
                $response .= '<div class="dropdown pull-right btn-post-options-wrapper">';
                $response .= '<button class="btn btn-default dropdown-toggle btn-post-options btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true"><i class="fa fa-angle-down"></i></button>';
                $response .= '<ul class="dropdown-menu">';
                if ($post['pinned'] == 0) {
                    $response .= '<li><a href="#" onclick="pin_post(' . $post['postid'] . '); return false;">' . _l('newsfeed_pin_post') . '</a></li>';
                } else {
                    $response .= '<li><a href="#" onclick="unpin_post(' . $post['postid'] . '); return false;">' . _l('newsfeed_unpin_post') . '</a></li>';
                }
                $response .= '<li><a href="#" onclick="delete_post(' . $post['postid'] . '); return false;">' . _l('newsfeed_delete_post') . '</a></li>';
                $response .= '</ul>';
                $response .= '</div>';
            }
            $response .= '<small class="text-muted">' . _l('newsfeed_published_post') . ': ' . _dt($post['datecreated']) . '</small>';
            $response .= '</div>';
            $response .= '</div>'; // media end
            $response .= '<div class="post-content mtop20 display-block">';
            if (!empty($visible_departments)) {
                $visible_departments = substr($visible_departments, 0, -2);
                $response .= '<i class="fa fa-question-circle" data-toggle="tooltip" data-title="' . _l('newsfeed_newsfeed_post_only_visible_to_departments', $visible_departments) . '"></i> ';
            }
            $response .= check_for_links($post['content']);
            $response .= '<div class="clearfix mbot10"></div>';
            $image_attachments       = $this->newsfeed_model->get_post_attachments($post['postid'], true);
            $total_image_attachments = count($image_attachments);
            $non_image_attachments   = $this->newsfeed_model->get_post_attachments($post['postid']);

            if ($total_image_attachments > 0) {
                $response .= '<hr />';
                $response .= '<ul class="list-unstyled">';
                $a = 0;
                foreach ($image_attachments as $attachment) {
                    $_wrapper_additional_class = ' ';
                    if ($total_image_attachments <= 3) {
                        $_wrapper_additional_class .= 'post-image-wrapper-' . $total_image_attachments . ' ';
                    }
                    $response .= '<div class="post-image-wrapper ' . $_wrapper_additional_class . 'mbot10">';
                    $response .= '<a href="' . base_url('uploads/newsfeed/' . $post['postid'] . '/' . $attachment['file_name']) . '" data-lightbox="post-' . $post['postid'] . '"><img src="' . base_url('uploads/newsfeed/' . $post['postid'] . '/' . $attachment['file_name']) . '" class="img img-responsive"></a>';
                    $response .= '</div>';
                    if ($a == 5) {
                        $total_left = $total_image_attachments - 6;
                        if ($total_left > 0) {
                            $next_image_attachment_url = base_url('uploads/newsfeed/' . $post['postid'] . '/' . $image_attachments[$a + 1]['file_name']);
                            $response .= '<div class="clearfix"></div><a href="' . $next_image_attachment_url . '" class="pull-right" data-lightbox="post-' . $post['postid'] . '">+' . $total_left . ' more</a>';
                            break;
                        }
                    }
                    $a++;
                }
                // Hidden images for +X left lightbox
                for ($i = $a + 2; $i < $total_image_attachments; $i++) {
                    $response .= '<a href="' . base_url('uploads/newsfeed/' . $post['postid'] . '/' . $image_attachments[$i]['file_name']) . '" data-lightbox="post-' . $post['postid'] . '"></a>';
                }
                $response .= '</ul>';
            }
            if (count($non_image_attachments) > 0) {
                if ($total_image_attachments == 0) {
                    $response .= '<hr />';
                }
                $response .= '<div class="clearfix"></div>';
                $response .= '<ul class="list-unstyled">';
                foreach ($non_image_attachments as $attachment) {
                    $response .= '<li><i class="' . get_mime_class($attachment['filetype']) . '"></i> <a href="' . site_url('download/file/newsfeed/' . $attachment['id']) . '">' . $attachment['file_name'] . '</a></li>';
                }
                $response .= '<ul>';
            }
            $response .= '</div>';
            $response .= '</div>'; // panel body end
            $response .= '<div class="post_likes_wrapper" data-likes-postid="' . $post['postid'] . '">';
            $response .= $this->init_post_likes($post['postid']);
            $response .= '</div>';
            // Comments
            $response .= '<div class="post_comments_wrapper" data-comments-postid="' . $post['postid'] . '">';
            $response .= $this->init_post_comments($post['postid']);
            $response .= '</div>';
            $response .= '<div class="panel-footer user-comment">';
            $response .= '<div class="pull-left comment-image">';
            $response .= '<a href="' . admin_url('profile/' . $post['creator']) . '">' . staff_profile_image(get_staff_user_id(), array(
                'staff-profile-image-small',
                'no-radius'
            )) . '</a>';
            $response .= '</div>'; // end comment-image
            $response .= '<div class="media-body comment-input">';
            $response .= '<input type="text" class="form-control input-sm" placeholder="' . _l('comment_this_post_placeholder') . '" data-postid="' . $post['postid'] . '">';
            $response .= '</div>'; // end comment-input
            $response .= '</div>'; // end user-comment
            $response .= '</div>'; // panel end
        }

        echo $response;
    }
    /* Init post likes to post */
    public function init_post_likes($id)
    {
        $_likes = '';
        $_likes .= '<div class="panel-footer user-post-like">';
        if (!$this->newsfeed_model->user_liked_post($id)) {
            $_likes .= '<button type="button" class="btn btn-default btn-icon" onclick="like_post(' . $id . ')"> <i class="fa fa-heart"></i></button>';
        } else {
            $_likes .= '<button type="button" class="btn btn-danger btn-icon" onclick="unlike_post(' . $id . ')"> <i class="fa fa-heart-o"></i></button>';
        }
        $_likes .= '</div>';
        if (total_rows('tblpostlikes', array(
            'postid' => $id
        ))) {
            $_likes .= '<div class="panel-footer post-likes">';
            $total_post_likes = total_rows('tblpostlikes', array(
                'postid' => $id
            ));
            $this->db->select();
            $this->db->from('tblpostlikes');
            $this->db->join('tblstaff', 'tblstaff.staffid = tblpostlikes.userid', 'left');
            $this->db->where('userid !=', get_staff_user_id());
            $this->db->where('postid', $id);
            $this->db->order_by('dateliked', 'asc');
            $likes       = $this->db->get()->result_array();
            $total_likes = count($likes);
            $total_pages = $total_likes / $this->newsfeed_model->post_likes_limit;
            $likes_modal = '<a href="#" onclick="return false;" data-toggle="modal" data-target="#modal_post_likes" data-postid="' . $id . '" data-total-pages="' . $total_pages . '">';
            if ($this->newsfeed_model->user_liked_post($id) && $total_post_likes == 1) {
                $_likes .= _l('newsfeed_you_like_this');
            } else if (($this->newsfeed_model->user_liked_post($id) && $total_post_likes > 1) || ($this->newsfeed_model->user_liked_post($id) && $total_post_likes >= 2)) {
                if ($total_likes == 1) {
                    $_likes .= _l('newsfeed_you_and') . ' ' . $likes[0]['firstname'] . ' ' . $likes[0]['lastname'] . ' ' . _l('newsfeed_like_this');
                } else if ($total_likes == 2) {
                    $_likes .= _l('newsfeed_you') . ', ' . $likes[0]['firstname'] . ' ' . $likes[0]['lastname'] . ' and ' . $likes[1]['firstname'] . ' ' . $likes[1]['lastname'] . _l('newsfeed_like_this');
                } else {
                    $_likes .= 'You, ' . $likes[0]['firstname'] . ' ' . $likes[0]['lastname'] . ', ' . $likes[1]['firstname'] . ' ' . $likes[1]['lastname'] . ' and ' . $likes_modal . ' ' . ($total_likes - 2) . ' ' . _l('newsfeed_one_other') . '</a> ' . _l('newsfeed_like_this');
                }
            } else {
                $i = 1;
                foreach ($likes as $like) {
                    if ($i > 3) {
                        $_total_likes = ($total_likes - 3);
                        if ($_total_likes != 0) {
                            $_likes = substr($_likes, 0, -2);
                            $_likes .= $likes_modal . ' ' . _l('newsfeed_and') . ' ' . $_total_likes . ' </a>' . _l('newsfeed_like_this');
                        } else {
                            $_likes = substr($_likes, 0, -2) . ' ' . _l('newsfeed_like_this');
                        }
                        break;
                    } else {
                        $_likes .= $like['firstname'] . ' ' . $like['lastname'] . ', ';
                    }
                    $i++;
                }
                if ($i < 4) {
                    $_likes = substr($_likes, 0, -2);
                    $_likes .= ' ' . _l('newsfeed_like_this');
                }
            }
            $_likes .= '</div>'; // panel footer
        }
        if ($this->input->is_ajax_request() && $this->input->get('refresh_post_likes')) {
            echo $_likes;
        } else {
            return $_likes;
        }
    }
    /* Init post comments */
    public function init_post_comments($id)
    {
        $_comments      = '';
        $total_comments = total_rows('tblpostcomments', array(
            'postid' => $id
        ));
        if ($total_comments > 0) {
            $page = $this->input->post('page');
            if (!$this->input->post('page')) {
                $_comments .= '<div class="panel-footer post-comment">';
            }
            $comments            = $this->newsfeed_model->get_post_comments($id, $page);
            // Add +1 becuase the first page is already inited
            $total_comment_pages = ($total_comments / $this->newsfeed_model->post_comments_limit) + 1;
            foreach ($comments as $comment) {
                $_comments .= $this->comment_single($comment);
            }
            if ($total_comments > $this->newsfeed_model->post_comments_limit && !$this->input->post('page')) {
                $_comments .= '<a href="#" onclick="load_more_comments(this); return false" class="mtop10 load-more-comments display-block" data-postid="' . $id . '" data-total-pages="' . $total_comment_pages . '"><input type="hidden" name="page" value="1">' . _l('newsfeed_show_more_comments') . '</a>';
            }
            if (!$this->input->post('page')) {
                $_comments .= '</div>'; // end comments footer
            }
        }
        if (($this->input->is_ajax_request() && $this->input->get('refresh_post_comments')) || ($this->input->is_ajax_request() && $this->input->post('page'))) {
            echo $_comments;
        } else {
            return $_comments;
        }
    }
    public function comment_single($comment)
    {
        $_comments = '';
        $_comments .= '<div class="comment" data-commentid="' . $comment['id'] . '">';
        $_comments .= '<div class="pull-left comment-image">';
        $_comments .= '<a href="' . admin_url('profile/' . $comment['userid']) . '">' . staff_profile_image($comment['userid'], array(
            'staff-profile-image-small',
            'no-radius'
        )) . '</a>';
        $_comments .= '</div>'; // end comment-image
        if ($comment['userid'] == get_staff_user_id() || is_admin()) {
            $_comments .= '<span class="pull-right"><a href="#" class="remove-post-comment" onclick="remove_post_comment(' . $comment['id'] . ',' . $comment['postid'] . '); return false;"><i class="fa fa-remove bold"></i></span></a>';
        }
        $_comments .= '<div class="media-body">';
        $_comments .= '<p class="no-margin comment-content"><a href="' . admin_url('profile/' . $comment['userid']) . '">' . get_staff_full_name($comment['userid']) . '</a> ' . check_for_links($comment['content']) . '</p>';
        $total_comment_likes = total_rows('tblcommentlikes', array(
            'commentid' => $comment['id'],
            'postid' => $comment['postid']
        ));
        $total_pages         = $total_comment_likes / $this->newsfeed_model->post_comments_limit;
        $likes_modal         = '<a href="#" onclick="return false;" data-toggle="modal" data-target="#modal_post_comment_likes" data-commentid="' . $comment['id'] . '" data-total-pages="' . $total_pages . '">';
        $_comment_likes      = '';
        if ($total_comment_likes > 0) {
            $_comment_likes = ' - ' . $likes_modal . $total_comment_likes . ' <i class="fa fa-thumbs-o-up"></i></a>';
        } else {
            $_comment_likes .= '</a>';
        }
        if (!$this->newsfeed_model->user_liked_comment($comment['id'])) {
            $_comments .= '<p class="no-margin"><a href="#" onclick="like_comment(' . $comment['id'] . ',' . $comment['postid'] . '); return false;"><small>' . _l('newsfeed_like_this_saying') . ' ' . $_comment_likes . ' - ' . _dt($comment['dateadded']) . '</small></p>';
        } else {
            $_comments .= '<p class="no-margin"><a href="#" onclick="unlike_comment(' . $comment['id'] . ',' . $comment['postid'] . '); return false;"><small>' . _l('newsfeed_unlike_this_saying') . ' ' . $_comment_likes . ' - ' . _dt($comment['dateadded']) . '</small></p>';
        }
        $_comments .= '</div>';
        $_comments .= '</div>';
        $_comments .= '<div class="clearfix"></div>';

        return $_comments;
    }
    public function get_data()
    {
        $this->load->model('departments_model');
        $data['departments'] = $this->departments_model->get();
        $this->load->view('admin/includes/modals/newsfeed_form', $data);
    }
    /* Likes modal to see all post likes */
    public function load_likes_modal()
    {
        if ($this->input->post()) {
            $likes  = $this->newsfeed_model->load_likes_modal($this->input->post('page'), $this->input->post('postid'));
            $_likes = '';
            foreach ($likes as $like) {
                $_likes .= '<div class="pull-left modal_like_area"><a href="' . admin_url('profile/' . $like['userid']) . '" target="_blank">' . staff_profile_image($like['userid'], array(
                    'staff-profile-image-small',
                    'no-radius',
                    'pull-left'
                )) . '</a>
                <div class="media-body">
                 <a href="' . admin_url('profile/' . $like['userid']) . '" target="_blank">' . get_staff_full_name($like['userid']) . '</a>
             </div>
         </div></div>';
            }
            echo $_likes;
        }
    }
    /* Comment likes modal to see all comment likes */
    public function load_comment_likes_model()
    {
        if ($this->input->post()) {
            $likes     = $this->newsfeed_model->load_comment_likes_model($this->input->post('page'), $this->input->post('commentid'));
            $_comments = '';
            foreach ($likes as $like) {
                $_comments .= '<div class="pull-left modal_like_area"><a href="' . admin_url('profile/' . $like['userid']) . '" target="_blank">' . staff_profile_image($like['userid'], array(
                    'staff-profile-image-small',
                    'no-radius'
                )) . '</a>
            <div class="media-body">
             <a href="' . admin_url('profile/' . $like['userid']) . '" target="_blank">' . get_staff_full_name($like['userid']) . '</a>
          </div>
      </div></div>';
            }
            echo $_comments;
        }
    }
    /* Add new newsfeed post */
    public function add_post()
    {
        if ($this->input->post()) {
            $postid = $this->newsfeed_model->add($this->input->post());
            if ($postid) {
                echo json_encode(array(
                    'postid' => $postid
                ));
            }
        }
    }
    /* Will pin post to top */
    public function pin_newsfeed_post($id)
    {
        do_action('before_pin_post', $id);
        echo json_encode(array(
            'success' => $this->newsfeed_model->pin_post($id)
        ));
        $this->session->set_flashdata('newsfeed_auto', true);
    }
    /* Will unpim post from top */
    public function unpin_newsfeed_post($id)
    {
        do_action('before_unpin_post', $id);
        echo json_encode(array(
            'success' => $this->newsfeed_model->unpin_post($id)
        ));
        $this->session->set_flashdata('newsfeed_auto', true);
    }
    /* Add post attachments */
    public function add_post_attachments($id)
    {
        $this->load->helper('perfex_upload');
        handle_newsfeed_post_attachments($id);
    }
    /* Staff click like button*/
    public function like_post($id)
    {
        echo json_encode(array(
            'success' => $this->newsfeed_model->like_post($id)
        ));
    }
    /* Staff unlike post */
    public function unlike_post($id)
    {
        echo json_encode(array(
            'success' => $this->newsfeed_model->unlike_post($id)
        ));
    }
    /* Post new comment by staff */
    public function add_comment()
    {
        $comment_id = $this->newsfeed_model->add_comment($this->input->post());
        $success    = ($comment_id !== FALSE ? TRUE : FALSE);
        $comment    = '';
        if ($comment_id) {
            $comment = $this->comment_single($this->newsfeed_model->get_comment($comment_id, true));
        }
        echo json_encode(array(
            'success' => $success,
            'comment' => $comment
        ));
    }
    /* Like post comment */
    public function like_comment($id, $postid)
    {
        $success = $this->newsfeed_model->like_comment($id, $postid);
        $comment = $this->comment_single($this->newsfeed_model->get_comment($id, true));
        echo json_encode(array(
            'success' => $success,
            'comment' => $comment
        ));
    }
    /* Unlike post comment */
    public function unlike_comment($id, $postid)
    {
        $success = $this->newsfeed_model->unlike_comment($id, $postid);
        $comment = $this->comment_single($this->newsfeed_model->get_comment($id, true));
        echo json_encode(array(
            'success' => $success,
            'comment' => $comment
        ));
    }
    /* Delete post comment */
    public function remove_post_comment($id, $postid)
    {
        echo json_encode(array(
            'success' => $this->newsfeed_model->remove_post_comment($id, $postid)
        ));
    }
    /* Delete all post */
    public function delete_post($postid)
    {
        do_action('before_delete_post', $postid);
        echo json_encode(array(
            'success' => $this->newsfeed_model->delete_post($postid)
        ));
    }
}
