<?php
/**
 * Esse é um arquivo de template
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Intranet Astra Child Theme
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$ips = intranet_fafar_api_get_ips();

$ips_110_in_use = array_filter( $ips, function ( $ip ) {
    $subnet = (int) explode( '.', $ip['data']['address'] )[2];
    return ( isset( $ip['data']['equipament_id'] ) && $subnet === 110 );
} );

$ips_111_in_use = array_filter( $ips, function ( $ip ) {
    $subnet = (int) explode( '.', $ip['data']['address'] )[2];
    return ( isset( $ip['data']['equipament_id'] ) && $subnet === 111 );
} );

$total_ips_in_use = $ips_110_in_use + $ips_111_in_use;

$TOTAL_110_ADDRESS = 253;
$TOTAL_111_ADDRESS = 253;

$ips_110_usage_percentage   = count( $ips_110_in_use ) / $TOTAL_110_ADDRESS * 100;
$ips_111_usage_percentage   = count( $ips_111_in_use ) / $TOTAL_111_ADDRESS * 100;
$total_ips_usage_percentage = count( $total_ips_in_use ) / count( $ips ) * 100;

get_header(); ?>

<?php if ( astra_page_layout() == 'left-sidebar' ) : ?>

	<?php get_sidebar(); ?>

<?php endif ?>

	<div id="primary" <?php astra_primary_class(); ?>>

		<?php astra_primary_content_top(); ?>

        <?php astra_content_page_loop(); ?>

<!--
    *
    *
    *
    * Conteúdo customizado da página
    * Início
--> 
    
        <!-- HEADER BUTTONS -->

        <div class="d-flex justify-content-start gap-2 mb-4">
            <a href="/adicionar-ip" class="btn btn-outline-success text-decoration-none">
                <i class="bi bi-plus-lg"></i>
                Adicionar
            </a>
        </div>

        <!-- CHARTS -->

        <!--<div class="d-flex justify-content-around mb-4">
            <div class="card" style="width: 18rem;">intranet_fafar_importar_json
                <canvas id="myChart1"></canvas>
                <div class="card-body">
                    <h5 class="card-title">Chart vs Mês</h5>
                </div>
            </div>

            <div class="card" style="width: 18rem;">
                <canvas id="myChart2"></canvas>
                <div class="card-body">
                    <h5 class="card-title">Chart vs Ano</h5>
                </div>
            </div>

            <div class="card" style="width: 18rem;">
                <canvas id="myChart3"></canvas>
                <div class="card-body">
                    <h5 class="card-title">Chart vs Setor</h5>
                </div>
            </div>
        </div>-->

        <!-- TABS -->

        <!--<ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="#">Active</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Link</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Link</a>
            </li>
            <li class="nav-item">
                <a class="nav-link disabled" aria-disabled="true">Disabled</a>
            </li>
        </ul>-->

        <!-- STATS -->

        <h5>Uso dos IPs</h5>
        <div class="mb-3 mt-3 d-flex flex-column gap-1">
            <span>Total</span>
            <div class="progress" 
                 role="progressbar" 
                 aria-label="Barra de progresso do uso de ips" 
                 aria-valuenow="<?= number_format( $total_ips_usage_percentage, 2 ) ?>" 
                 aria-valuemin="0" 
                 aria-valuemax="100">
                <div class="progress-bar bg-warning overflow-visible text-dark" style="width: <?= $total_ips_usage_percentage ?>%">
                    <?= number_format( $total_ips_usage_percentage, 2 ) ?>%
                </div>
            </div>
            <span>150.164.110.0/24</span>
            <div class="progress" 
                 role="progressbar" 
                 aria-label="Barra de progresso do uso de ips" 
                 aria-valuenow="<?= number_format( $ips_110_usage_percentage, 2 ) ?>" 
                 aria-valuemin="0" 
                 aria-valuemax="100">
                <div class="progress-bar bg-primary overflow-visible text-dark" style="width: <?= $ips_110_usage_percentage ?>%">
                    <?= number_format( $ips_110_usage_percentage, 2 ) ?>%
                </div>
            </div>
            <span>150.164.111.0/24</span>
            <div class="progress" 
                 role="progressbar" 
                 aria-label="Barra de progresso do uso de ips" 
                 aria-valuenow="<?= number_format( $ips_111_usage_percentage, 2 ) ?>" 
                 aria-valuemin="0" 
                 aria-valuemax="100">
                <div class="progress-bar bg-danger overflow-visible text-dark" style="width: <?= $ips_111_usage_percentage ?>%">
                    <?= number_format( $ips_111_usage_percentage, 2 ) ?>%
                </div>
            </div>
            <div class="d-flex gap-5">
                <small class="fst-italic">Total: <?= count( $ips_110_in_use + $ips_111_in_use ) . '/' . count( $ips ) ?></small>
                <small class="fst-italic">Sub-rede 110: <?= count( $ips_110_in_use ) . '/' . $TOTAL_110_ADDRESS ?></small>
                <small class="fst-italic">Sub-rede 111: <?= count( $ips_111_in_use ) . '/' . $TOTAL_111_ADDRESS ?></small>
            </div>
        </div>

        <!-- TABLES -->

        <div id="table-wrapper"></div>
        
<!--
    * Conteúdo customizado da página
    * Fim
    *
    *
    *
-->    

		<?php astra_primary_content_bottom(); ?>

	</div><!-- #primary -->

<?php if ( astra_page_layout() == 'right-sidebar' ) : ?>

	<?php get_sidebar(); ?>

<?php endif ?>

<?php get_footer(); ?>
