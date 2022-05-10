<?php
/**
 * @package     Intext Ads, a Joomla plugin
 *
 * @author      Pavel Syomin
 * @copyright   Copyright © Pavel Syomin, 2022 
 * @license     GNU General Public License version 3; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die();

use Joomla\CMS\Plugin\CMSPlugin;

class plgContentIntextads extends CMSPlugin
{

	protected $app;

    public function onContentPrepare($context, &$row, &$params, $page = 0)
    {   
        // Do not show ads in API mode
        if ($this->app->isClient('api'))
        {
            return;
        }
        
        // Show only in articles
        if ($context == 'com_content.article')
        {
            // Skip articles with a snippet disabling ads
            if (strpos($row->text, '{intextads:off}') !== false
                or strpos($row->text, '{intextads: off}') !== false)
            {
                return;
            }
            
            // Get ad blocks from plugin settings
            $blocks = [];
            for ($i == 1; $i <= 5; $i++)
            {
                $block = trim($this->params->get("block$i", ""));
                if ($block != '')
                {
                    $blocks[] = $block;
                }
            }
            
            if (count($blocks) == 0)
            {
                return;
            }
            
            // Place blocks in article text
            $this->_place($blocks, $row);
            
            // Add custom CSS to document head
            $this->_addCSS();
        } 
	}
    
    private function _place($blocks, &$row)
    {
        // Calculate the number of blocks to add
        $textLength = strlen($row->text);
        if ($textLength <= 25000)
        {
            $maxNBlocks = intdiv($textLength, 5000);
        }
        else
        {
            $maxNBlocks = 5;
        }
        $nBlocks = min(count($blocks), $maxNBlocks);
        
        if ($nBlocks == 0)
        {
            return;
        }
        
        // Calculate blocks frequency
        $nParagraphs = substr_count($row->text, '<p');
        $startOffset = $this->params->get('startOffset', 5);
        $endOffset = $this->params->get('endOffset', 10);
        $nParagraphsAvail = $nParagraphs - $startOffset - $endOffset;
        $blocksFreq = intdiv($nParagraphsAvail, $nBlocks);
        
        if ($nParagraphs < 5 || $nParagraphsAvail < 0)
        {
            return;
        }
        
        // Add blocks using calculated start and frequency
        $pos = strpos($row->text, '<p');
        $count = 0;
        $blocksClass = $this->params->get('blocksClass', 'intext-ads');
        while ($pos !== false)
        {
            $offset = $pos + strlen('<p');
            $pos = strpos($row->text, '<p', $offset);
            $count++;
            if ($count == $startOffset)
            {
                $block = array_shift($blocks);
                $block = "<div class=\"$blocksClass\">\n" . $block . "\n</div>\n";
                $row->text = substr_replace($row->text, $block, $pos, 0);
                $startOffset += $blocksFreq;
                $nBlocks--;
                if ($nBlocks == 0)
                {
                    break;
                }
            }
        }        
    }
    
    private function _addCSS()
    {
        $css = $this->params->get('css', '');
        $this->app->getDocument()->addStyleDeclaration($css);     
    }
}
