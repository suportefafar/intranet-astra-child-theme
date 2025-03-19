<?php
// Prevent direct access to the file.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function intranet_fafar_mail_on_create_service_ticket( $form_data ) {

  if( ! isset( $form_data['object_name'] ) ) return $form_data;

  if ( $form_data['object_name'] !== 'service_ticket' ) return $form_data;

  $form_data['data'] = json_decode( $form_data['data'], true );

  $user_info  = get_userdata( $form_data['owner'] );
  $user_email = $user_info ? $user_info->user_email : '';

  $subject = '✅ Sua Ordem De Serviço Foi Criado Com Sucesso!';

  $departament = $form_data['data']['departament_assigned_to'][0];
  if ( isset( wp_roles()->roles[ $departament ] ) ) {
      $departament = wp_roles()->roles[ $departament ]['name'];
  }

  $place_desc = '';
  if ( ! empty( $form_data['data']['place'][0] ) ) {
    $place = intranet_fafar_api_get_submission_by_id( $form_data['data']['place'][0] );

    if ( ! empty( $place['data'] ) ) {
      $place_desc = $place['data']['number'] . ( ! empty( $place['data']['desc'] ) ? ' ' . $place['data']['desc'] : '' );
    }
  }
  
  $message = '
    <p>Sua ordem de serviço foi registrado com sucesso! 🎊 Estamos analisando sua solicitação e em breve entraremos em contato com mais informações.</p>
    <p><strong>Detalhes da Ordem de Serviço:</strong></p>
    <ul>
      <li><strong>Número:</strong> ' . $form_data['data']['number'] . '</li>
      <li><strong>Departamento:</strong> ' . $departament . '</li>
      <li><strong>Tipo:</strong> ' . $form_data['data']['type'] . '</li>
      <li><strong>Local:</strong> ' . $place_desc . '</li>
      <li><strong>Patrimônio:</strong> ' . $form_data['data']['asset'] . '</li>
      <li><strong>Relato:</strong> ' . $form_data['data']['user_report'] . '</li>
    </ul>
    <p>Você pode acompanhar o status do sua ordem de serviço a qualquer momento através da nossa intranet. Caso precise de mais alguma informação, não hesite em nos contatar.</p>
  ';

  intranet_fafar_mail_notify( $user_email, $subject, $message );

  $form_data['data'] = json_encode( $form_data['data'] );

  return $form_data;
}

function intranet_fafar_mail_on_create_service_ticket_update( $form_data ) {

  if( ! isset( $form_data['object_name'] ) ) return $form_data;

  if ( $form_data['object_name'] !== 'service_ticket_update' ) return $form_data;

  $form_data['data'] = json_decode( $form_data['data'], true );

  $service_ticket = null;
  if ( ! empty( $form_data['data']['service_ticket'] ) ) {
    $service_ticket = intranet_fafar_api_get_submission_by_id( $form_data['data']['service_ticket'] );
  } else {
    return array( 'error_msg' => 'Nenhum ID de ordem de serviço encontrado!' );
  }

  $user_email = ! empty( $service_ticket['owner']['data']->user_email ) ? $service_ticket['owner']['data']->user_email : '';

  $subject = '🔄 Atualização na sua OS #' . $service_ticket['data']['number'] . ' - Confira as Novidades!';

  $departament = $service_ticket['data']['departament_assigned_to'][0];
  if ( isset( wp_roles()->roles[ $departament ] ) ) {
      $departament = wp_roles()->roles[ $departament ]['name'];
  }

  $place_desc = '';
  if ( ! empty( $service_ticket['data']['place'][0] ) ) {
    $place = intranet_fafar_api_get_submission_by_id( $service_ticket['data']['place'][0] );

    if ( ! empty( $place['data'] ) ) {
      $place_desc = $place['data']['number'] . ( ! empty( $place['data']['desc'] ) ? ' ' . $place['data']['desc'] : '' );
    }
  }
  
  $message = '
    <p>Sua ordem de serviço foi atualizado! 🚀 Aqui estão as últimas informações sobre o andamento da sua solicitação:</p>
    <p><strong>Novo Status:</strong></p>
    <ul>
      <li><strong>Status:</strong> ' . $form_data['data']['status'][0] . '</li>
      <li><strong>Relatório:</strong> ' . $form_data['data']['service_report'] . '</li>
    </ul>
    <p><strong>Detalhes da Ordem de Serviço:</strong></p>
    <ul>
      <li><strong>Número:</strong> ' . $service_ticket['data']['number'] . '</li>
      <li><strong>Departamento:</strong> ' . $departament . '</li>
      <li><strong>Tipo:</strong> ' . $service_ticket['data']['type'] . '</li>
      <li><strong>Local:</strong> ' . $place_desc . '</li>
      <li><strong>Patrimônio:</strong> ' . $service_ticket['data']['asset'] . '</li>
      <li><strong>Relato:</strong> ' . $service_ticket['data']['user_report'] . '</li>
      <li><strong>Criado em:</strong> ' . convertToBrazilianFormat( $service_ticket['created_at'] ) . '</li>
    </ul>
    <p>Você pode acompanhar o status do sua ordem de serviço a qualquer momento através da nossa intranet. Caso precise de mais alguma informação, não hesite em nos contatar.</p>
  ';

  intranet_fafar_mail_notify( $user_email, $subject, $message );

  $form_data['data'] = json_encode( $form_data['data'] );

  return $form_data;
}

function intranet_fafar_mail_notify( $to, $subject, $message, $headers = null, $attachments = null ) {

  error_log( print_r( array( $to, $subject, $message, $headers, $attachments ), true) );

  $html_mail_body_template = '
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Atualizações na Intranet - Faculdade de Farmácia da UFMG</title>
    </head>
    <body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0;">
        <table width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
            <!-- Header -->
            <tr>
                <td style="background-color: #404040; color: #ffffff; padding: 20px; text-align: center;">
                    <table width="100%" cellspacing="0" cellpadding="0" border="0">
                        <tr>
                            <td width="64" align="center">
                                <img src="https://intranet.farmacia.ufmg.br/wp-content/uploads/2025/03/logo-fafar-white-gray.png" alt="Ícone da Faculdade de Farmácia da UFMG" style="display: block; width: 64px; height: 64px;">
                            </td>
                            <td align="left" style="font-size: 24px; padding-left: 10px; font-weight: bold;">Faculdade de Farmácia da UFMG</td>
                        </tr>
                    </table>
                </td>
            </tr>

            <!-- Content -->
            <tr>
                <td style="padding: 20px; color: #333333; font-size: 16px; line-height: 1.6;">
                    <p>Prezado(a),</p>
                    <p>' . $message . '</p>
                    <p>Atenciosamente,<br>Equipe de Suporte da Faculdade de Farmácia da UFMG</p>
                </td>
            </tr>

            <!-- Footer -->
            <tr>
                <td style="background-color: #f1f1f1; text-align: center; padding: 10px; font-size: 14px; color: #666666;">
                    <p>Este é um e-mail automático. Por favor, não responda diretamente a esta mensagem.</p>
                    <p>Dúvidas? Entre em contato: <a href="mailto:suporte@farmacia.ufmg.br" style="color: #004a8d; text-decoration: none;">suporte@farmacia.ufmg.br</a></p>
                </td>
            </tr>
        </table>
    </body>
    </html>';

    if ( ! is_email( $to ) ) {
      intranet_fafar_logs_register_log(
          'ERROR',
          'intranet_fafar_mail_notify',
          json_encode(
              array(
                  'func' => 'intranet_fafar_mail_notify',
                  'msg'  => 'Error to send email: Not a valid address',
                  'obj'  => $to ?? '--',
              ),
          ),
      );

      return false;
    }

    if ( empty( $headers ) ) {
      $headers[] = 'Content-Type: text/html; charset=UTF-8';
    }

    $result = wp_mail( $to, $subject, $html_mail_body_template, $headers, $attachments );

    if ( ! $result ) {
      intranet_fafar_logs_register_log(
        'ERROR',
        'intranet_fafar_mail_notify',
        json_encode(
            array(
                'func' => 'intranet_fafar_mail_notify',
                'msg'  => 'Error to send email',
                'obj'  => array(
                  'to'          => $to,
                  'subject'     => $subject,
                  'message'     => $message,
                  'headers'     => $headers,
                  'attachments' => $attachments,
                ),
            ),
        ),
      );
    }
}