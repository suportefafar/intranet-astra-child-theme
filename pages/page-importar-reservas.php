<?php
/**
 * Esse é um arquivo de template
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Intranet Astra Child Theme
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

global $wpdb;

$table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';

if (isset($_POST["json_string"])) {

    $json_string = $_POST["json_string"];

    print_r("Processando.....");
    print_r("<br/>");

    $json_d = json_decode(stripslashes($json_string));

    print_r("<br/>");
    print_r("JSON LAST ERROR: ");
    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            echo 'No errors';
            break;
        case JSON_ERROR_DEPTH:
            echo 'Maximum stack depth exceeded';
            break;
        case JSON_ERROR_STATE_MISMATCH:
            echo 'Underflow or the modes mismatch';
            break;
        case JSON_ERROR_CTRL_CHAR:
            echo 'Unexpected control character found';
            break;
        case JSON_ERROR_SYNTAX:
            echo 'Syntax error, malformed JSON';
            break;
        case JSON_ERROR_UTF8:
            echo 'Malformed UTF-8 characters, possibly incorrectly encoded';
            break;
        default:
            echo 'Unknown error';
            break;
    }

    print_r("<br/>");

    /* 
     * 1 - Importar as disciplinas,
     */
    $class_subjects = intranet_fafar_api_get_submissions_by_object_name('class_subject');
    if ($class_subjects['error_msg'])
        $class_subjects = array();

    $total_rows = count($json_d);
    $count = 0;
    foreach ($json_d as $json_item) {

        print_r($json_item);
        print_r("<br/>");
        print_r((($count++) / $total_rows * 100) . "%");
        print_r("<br/>");

        $new_class_subject = array(
            'code' => '',
            'type' => '',
            'group' => '',
            'level' => '',
            'course' => '',
            'adjustment' => '',
            'professors' => '',
            'course_load' => '',
            'departament' => '',
            'name_of_subject' => '',
            'nature_of_subject' => '',
            'credits_of_subject' => '',
            'number_vacancies_offered' => '',
            'version_of_curriculum_matrix' => ''
        );
    }

}

function has_class_subject($class_subject, $class_subjects)
{

    /*
     * Se já não existe por CÓDIGO, NOME e TURMA 
     * Obs.: No caso do NOME, aplicar low case, retirar acentos e espaços
     */
    foreach ($class_subjects as $item) {

        // Códigos
        $code_a = intranet_fafar_utils_remove_accents($class_subject['code']);
        $code_a = strtolower($code_a);
        $code_a = str_replace(' ', '', $code_a);

        $code_b = intranet_fafar_utils_remove_accents($item['code']);
        $code_b = strtolower($code_b);
        $code_b = str_replace(' ', '', $code_b);

        // Nomes
        $name_a = intranet_fafar_utils_remove_accents($class_subject['name_of_subject']);
        $name_a = strtolower($name_a);
        $name_a = str_replace(' ', '', $name_a);

        $name_b = intranet_fafar_utils_remove_accents($item['name_of_subject']);
        $name_b = strtolower($name_b);
        $name_b = str_replace(' ', '', $name_b);

        // Turmas
        $group_a = intranet_fafar_utils_remove_accents($class_subject['group']);
        $group_a = strtolower($group_a);
        $group_a = str_replace(' ', '', $group_a);

        $group_b = intranet_fafar_utils_remove_accents($item['group']);
        $group_b = strtolower($group_b);
        $group_b = str_replace(' ', '', $group_b);

        if (
            $code_a === $code_b &&
            $name_a === $name_b &&
            $group_a === $group_b
        )
            return true;

    }

    return false;

}


get_header(); ?>

<?php if (astra_page_layout() == 'left-sidebar'): ?>

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

    <form class="my-3" action="/importar-reservas" method="POST">

        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="floatingInput" name="input_object_name" />
            <label for="floatingInput">Nome do Objeto</label>
        </div>

        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="fasd" name="input_owner" />
            <label for="fasd">Dono</label>
        </div>

        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="gsdge" name="input_group_owner" />
            <label for="gsdge">Grupo Dono</label>
        </div>

        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="asdasd" name="input_permissions" />
            <label for="asdasd">Permissões</label>
        </div>

        <div class="form-floating mb-3">
            <textarea class="form-control" placeholder="Insira o texto JSON aqui" id="floatingTextarea"
                name="json_string" rows="15" required></textarea>
            <label for="floatingTextarea">Texto JSON</label>
        </div>

        <button type="submit">
            <i class="bi bi-file-earmark-arrow-up"></i>
            Importar
        </button>

    </form>

    <!--
    * Conteúdo customizado da página
    * Fim
    *
    *
    *
-->

    <?php astra_primary_content_bottom(); ?>

</div><!-- #primary -->

<?php if (astra_page_layout() == 'right-sidebar'): ?>

    <?php get_sidebar(); ?>

<?php endif ?>

<?php get_footer(); ?>