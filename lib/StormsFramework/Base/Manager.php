<?php
/**
 * Storms Framework (http://storms.com.br/)
 *
 * @author    Vinicius Garcia | vinicius.garcia@storms.com.br
 * @copyright (c) Copyright 2012-2016, Storms Websolutions
 * @license   GPLv2 - GNU General Public License v2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @package   Storms
 * @version   3.0.0
 * 
 * StormsFramework\Base\Manager class
 * Classe base para as classes de customizacao,
 * registra as classes que foram carregadas no sistema StormsFramework\Base\Loader
 * As classes de customizacao que nao possuem hooks extendem esta classe
 * Source: http://code.tutsplus.com/series/object-oriented-programming-in-wordpress--cms-699
 */

namespace StormsFramework\Base;

use StormsFramework\Base;

class Manager
{
    protected $plugin_slug;
 
    protected $version;

    public function get_version() {
        return $this->version;
    }
	
	/**
	 * Armazena o slug do plugin carregado, sua versao e sua instancia
	 */
    public function __construct( $plugin_slug, $version, $component ) {
 
        $this->plugin_slug = $plugin_slug;
        $this->version = $version;
		$this->component = $component;
		
    }
}