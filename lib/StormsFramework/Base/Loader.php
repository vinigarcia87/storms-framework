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
 * StormsFramework\Base\Loader class
 * Classe responsavel por carregar os hooks definidos nas libs de customizacao
 * As classes de customizacao carregam a StormsFramework\Base\Loader para ativar os hooks
 * Source: http://code.tutsplus.com/series/object-oriented-programming-in-wordpress--cms-699
 */

namespace StormsFramework\Base;

class Loader
{
    protected $actions;

    protected $filters;

    protected $component;

    public function __construct( $component ) {

        $this->actions = array();
        $this->filters = array();

		$this->component = $component;
    }

	/**
	 * Adiciona actions
	 */
    public function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
        $this->actions = $this->add( $this->actions, $hook, $callback, $priority, $accepted_args );
		return $this;
    }

	/**
	 * Adiciona filters
	 */
    public function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
        $this->filters = $this->add( $this->filters, $hook, $callback, $priority, $accepted_args );
		return $this;
    }

    private function add( $hooks, $hook, $callback, $priority = 10, $accepted_args = 1 ) {

        $hooks[] = array(
            'hook'          => $hook,
            'component'     => $this->component,
            'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args

        );

        return $hooks;

    }

	/**
	 * Executa os filters e actions adicionados
	 */
    public function run() {

        foreach ( $this->filters as $hook ) {
            add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
        }

        foreach ( $this->actions as $hook ) {
            add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
        }

    }
}
