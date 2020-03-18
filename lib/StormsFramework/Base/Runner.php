<?php
/**
 * Storms Framework (http://storms.com.br/)
 *
 * @author    Vinicius Garcia | vinicius.garcia@storms.com.br
 * @copyright (c) Copyright 2012-2019, Storms Websolutions
 * @license   GPLv2 - GNU General Public License v2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @package   Storms
 * @version   4.0.0
 *
 * StormsFramework\Base\Runner class
 * Classe base para as classes de customizacao,
 * executa o trecho comum de interface com o StormsFramework\Base\Loader
 * As classes de customizacao que possuem hooks extendem esta classe
 * Source: http://code.tutsplus.com/series/object-oriented-programming-in-wordpress--cms-699
 */

namespace StormsFramework\Base;

use StormsFramework\Base;

class Runner extends Manager
{
    protected $loader;

	/**
	 * Executa o Loader
	 */
    public function run() {
		$this->loader->run();
    }

	/**
	 * Armazena o slug do plugin carregado, sua versao e sua instancia
	 * Executa o carregamento do Loader e os hooks definidos
	 */
    public function __construct( $plugin_slug, $version, $component ) {
        parent::__construct( $plugin_slug, $version, $component );

		$this->load_dependencies();
        $this->define_hooks();
    }

	/**
	 * Load Base\Loader class
	 */
    private function load_dependencies() {
		if(empty($this->loader))
			$this->loader = new Base\Loader($this->component);
    }

	/**
	 * Define os hooks da classe de customizacao
	 * Este metodo deve ser sobreescrito na classe que extende essa base
	 */
    public function define_hooks() {
		throw new \Exception( '\'' . __METHOD__ . '\' Not implemented for \'' . get_class( $this->component ) .'\'' );
    }
}
