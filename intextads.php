<?php

// No direct access to this file
defined('_JEXEC') or die();

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

class plgContentIntextads extends CMSPlugin
{

	protected $app;

    public function onContentPrepare($context, &$row, &$params, $page = 0)
    {        
        if ($this->app->isClient('api'))
        {
            return;
        }
        
        if ($context == 'com_content.article')
        {    
            $blocks = [];
            for ($i == 1; $i <= 5; $i++)
            {
                $block = trim($this->params->get("block_$i", ""));
                if ($block != '')
                {
                    $blocks[] = $block;
                }
            }
            
            if (count($blocks) == 0)
            {
                return;
            }
                        
            $this->_place($blocks, $row);
        } 
	}
    
    protected function _place($blocks, &$row)
    {
        $text_length = strlen($row->text);
        if ($text_length <= 25000)
        {
            $max_n_blocks = intdiv($text_length, 5000);
        }
        else
        {
            $max_n_blocks = 5;
        }
        $n_blocks = min(count($blocks), $max_n_blocks);
        
        if ($n_blocks == 0)
        {
            return;
        }
        
        $n_paragraphs = substr_count($row->text, '<p');
        $start_offset = 5;
        $end_offset = 10;
        $n_paragraphs_avail = $n_paragraphs - $start_offset - $end_offset;
        $blocks_freq = intdiv($n_paragraphs_avail, $n_blocks);
        
        if ($n_paragraphs < 5 || $n_paragraphs_avail < 0)
        {
            return;
        }
        
        $pos = strpos($row->text, '<p');
        $count = 0;
        while ($pos !== false)
        {
            $offset = $pos + 2;
            $pos = strpos($row->text, '<p', $offset);
            $count++;
            if ($count == $start_offset)
            {
                $block = array_shift($blocks);
                $block = "<div class=\"intext-ad\">\n" . $block . "\n</div>\n";
                $row->text = substr_replace($row->text, $block, $pos, 0);
                $start_offset += $blocks_freq;
                $n_blocks--;
                if ($n_blocks == 0)
                {
                    break;
                }
            }
        }
        
        $this->app->getDocument()->addStyleDeclaration('.intext-ad{margin-bottom: 1rem;}');     
    }
}
