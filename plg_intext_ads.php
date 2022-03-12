<?php

// No direct access to this file
defined('_JEXEC') or die();

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

class PlgContentIntextAds extends CMSPlugin
{
	/**
	 * @var    \Joomla\CMS\Application\SiteApplication
	 *
	 * @since  3.9.0
	 */
	protected $app;

	 public function onContentPrepare($context, &$row, &$params, $page = 0)
	 {
		/*
		 * Plugin code goes here.
		 * You can access parameters via $this->params
		 */
		if ($this->app->isClient('api') || $context === 'com_finder.indexer')
		{
			return;
		}
        
        if ($context !== 'com_content.article' && $view !== 'article' && $row->alias != 'golubie-ozera-aleksandrovska')
        {
            return;
        }
        
        $blocks_str = $this->params->get('blocks', '');
        if ($blocks_str == '')
        {
            return;
        }
        $blocks_str = str_replace(' ', '', $blocks_str);
        $blocks = explode(',', $blocks_str, $limit = 5);
        
        $this->_place($blocks, $row->text);       
	}
    
    protected function _place($blocks, &$text)
    {
        $text_length = strlen($text);
        if ($text_length <= 25000)
        {
            $max_n_blocks = intdiv($text_length, 5000);
        }
        else
        {
            $max_n_blocks = 5;
        }
        $n_blocks = min(count($blocks), $max_n_blocks);
        
        $n_paragraphs = substr_compare($text, '<p>');
        $start_offset = 5;
        $end_offset = 10;
        $n_paragraphs_avail = n_paragraphs - $start_offset - $end_offset;
        $blocks_freq = intdiv($n_paragraphs_avail, $n_blocks);
        
        $pos = strpos($text, '<p>');
        $count = 0;
        while ($pos !== false)
        {
            $offset = $pos + 3;
            $pos = strpos($text, '<p>', $offset)
            $count += 1;
            if ($count == $start_offset)
            {
                $block_id = array_shift($blocks);
                $block_id_str = "yandex_rtb_$block_id";
                substr_replace($text, "<div class=\"$block_id_str\"></div>\n<p>", $offset, 3);
                $start_offset += $blocks_freq;
            }
        }       
    }
}
?>
