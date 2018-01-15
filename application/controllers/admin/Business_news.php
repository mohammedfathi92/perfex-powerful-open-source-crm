<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Business_news extends Admin_controller
{
    private $rss_feed_sites = array(array('name' => 'Forbes', 'feed_url' => 'https://www.forbes.com/business/feed/'), array('name' => 'The Wall Street Journal', 'feed_url' => 'http://www.wsj.com/xml/rss/3_7085.xml'), array('name' => 'IB Times', 'feed_url' => 'http://www.ibtimes.com/rss/business'), array('name' => 'Enterpreneur', 'feed_url' => 'http://feeds.feedburner.com/entrepreneur/latest?format=xml'));

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        if (!is_connected()) {
            $data['rss_sites'] = array();
        } else {
            $data['rss_sites'] = do_action('before_get_rss_feeds', $this->rss_feed_sites);
        }

        $data['title'] = _l('business_news');
        $this->load->view('admin/business_news/list', $data);
    }
}
