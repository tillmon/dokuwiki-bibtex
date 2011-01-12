<?php
/**
 * DokuWiki Plugin bibtex (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Till Biskup <till@till-biskup>
 * @version 0.1
 * @date    2010-12-26
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once DOKU_PLUGIN.'syntax.php';

class syntax_plugin_bibtex_cite extends DokuWiki_Syntax_Plugin {
    public function getType() {
        return 'substition';
    }

    public function getPType() {
        return 'normal';
    }

    public function getSort() {
        return 32;
    }


    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\{\[.+?\]\}',$mode,'plugin_bibtex_cite');
    }

    public function handle($match, $state, $pos, &$handler){
        // Strip syntax and return only bibtex key(s)
		preg_match('/\{\[(.+?)\]\}/',$match,$matches);
        return array($matches[1], $state, $pos);
    }

    public function render($mode, &$renderer, $data) {
		@list($match, $state, $pos) = $data;
        global $ID;

        if($mode == 'xhtml') {
        
            require_once(DOKU_PLUGIN.'bibtex/lib/bibtexrender.php');
            $bibtexrenderer = bibtexrender_plugin_bibtex::getResource($ID);
			// Check whether the reference exists, otherwise silently ignore
			// The problem still exists when all keys in one block do not exist
            $bibkeys = explode(',',$match);
			if ((count($bibkeys) > 1) || $bibtexrenderer->printCitekey($match)) {
                $renderer->doc .= "[" ;
                foreach ($bibkeys as $bibkey) {
                    $renderer->doc .= '<a href="#ref__' . $bibkey . '" name="reft__' . $bibkey . '" id="reft__' . $bibkey . '" class="bibtex_citekey">';
                    $renderer->doc .= $bibtexrenderer->printCitekey($bibkey);
                    $renderer->doc .= '<span>';
                    $renderer->doc .= $bibtexrenderer->printReference($bibkey);
                    $renderer->doc .= '</span></a>';
                
                    // Suppress comma after last bibkey
                    // Alternatively, the output could be done after an implode
                    if ($bibkey != $bibkeys[sizeof($bibkeys)-1]) {
                        $renderer->doc .= ", ";
                    }
                }
                $renderer->doc .= "]";
            }

            return true;
            
        }
        return false;
    }
}

// vim:ts=4:sw=4:et: