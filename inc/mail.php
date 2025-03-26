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

  $subject = '✅ Sua Ordem De Serviço Foi Criado Com Sucesso!';

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
      <li><strong>Criado em:</strong> ' . intranet_fafar_utils_to_locale_datetime( $service_ticket['created_at'] ) . '</li>
    </ul>
    <p>Você pode acompanhar o status do sua ordem de serviço a qualquer momento através da nossa intranet. Caso precise de mais alguma informação, não hesite em nos contatar.</p>
  ';

  $subject = '🔄 Atualização na sua OS #' . $service_ticket['data']['number'] . ' - Confira as Novidades!';

  intranet_fafar_mail_notify( $user_email, $subject, $message );

  $form_data['data'] = json_encode( $form_data['data'] );

  return $form_data;
}

function intranet_fafar_mail_on_create_equipament( $form_data ) {
  if( ! isset( $form_data['object_name'] ) ) return $form_data;

  if ( $form_data['object_name'] !== 'equipament' ) return $form_data;

  $form_data['data'] = json_decode( $form_data['data'], true );

  // Verifica se tem patrimônio
  if ( empty( $form_data['data']['asset'] ) ) {
    $form_data['data'] = json_encode( $form_data['data'] );

    return $form_data;
  }

  // Responsável do equipamento
  $user_info      = get_userdata( $form_data['data']['applicant'][0] );
  $applicant_name = $user_info ? $user_info->display_name : '';

  // Local do equipamento
  $place_desc = '';
  if ( ! empty( $form_data['data']['place'][0] ) ) {
    $place = intranet_fafar_api_get_submission_by_id( $form_data['data']['place'][0] );

    if ( ! empty( $place['data'] ) ) {
      $place_desc = $place['data']['number'] . ( ! empty( $place['data']['desc'] ) ? ' ' . $place['data']['desc'] : '' );
    }
  }
  
  $message = '
    <p>Um novo equipamento com patrimônio foi cadastrado na Intranet FAFAR pelo nosso setor.</p>
    <p><strong>Detalhes do equipamento:</strong></p>
    <ul>
      <li><strong>Patrimônio:</strong> ' . $form_data['data']['asset'] . '</li>
      <li><strong>Responsável:</strong> ' . $applicant_name . '</li>
      <li><strong>Local:</strong> ' . $place_desc . '</li>
      <li><strong>Tipo:</strong> ' . $form_data['data']['object_sub_type'][0] . '</li>
      <li><strong>Descrição:</strong> ' . $form_data['data']['desc'] . '</li>
    </ul>
    <p>Caso precise de mais alguma informação, não hesite em nos contatar.</p>
  ';

  $subject = 'Novo equipamento com patrimônio';
  $to      = 'spatri@farmacia.ufmg.br';

  intranet_fafar_mail_notify( $to, $subject, $message );

  $form_data['data'] = json_encode( $form_data['data'] );

  return $form_data;
}

function intranet_fafar_mail_on_update_equipament( $form_data, $equipament_id ) {
  if ( ! isset( $form_data['object_name'] ) ) return $form_data;

  if ( $form_data['object_name'] !== 'equipament' ) return $form_data;

  // Checa se o ID do equipamento foi passado
  if ( empty( $equipament_id ) ) return $form_data;

  $old_equipament = intranet_fafar_api_get_submission_by_id( $equipament_id, false );

  // Checa se o existe o equipamento
  if ( empty( $old_equipament ) ) return $form_data;

  $form_data['data'] = json_decode( $form_data['data'], true );

  // Verifica se teve mudança de local
  $old_place         = ( ! empty( $old_equipament['data']['place'][0] ) ? $old_equipament['data']['place'][0] : '' );
  $new_place         = ( ! empty( $form_data['data']['place'][0] ) ? $form_data['data']['place'][0] : '' );
  $HAS_PLACE_CHANGED = ( $old_place !== $new_place );

  // Verifica se teve mudança de responsável
  $old_applicant         = ( ! empty( $old_equipament['data']['applicant'][0] ) ? $old_equipament['data']['applicant'][0] : '' );
  $new_applicant         = ( ! empty( $form_data['data']['applicant'][0] ) ? $form_data['data']['applicant'][0] : '' );
  $HAS_APPLICANT_CHANGED = ( $old_applicant !== $new_applicant );

  if ( ! $HAS_PLACE_CHANGED && ! $HAS_APPLICANT_CHANGED ) {
    $form_data['data'] = json_encode( $form_data['data'] );
    return $form_data;
  }

  // Verifica se tem patrimônio
  if ( empty( $form_data['data']['asset'] ) ) {
    $form_data['data'] = json_encode( $form_data['data'] );

    return $form_data;
  }

  // Configurando o tipo de do equipamento
  $equipament_type = ( ! empty( $form_data['data']['object_sub_type'][0] ) ? $form_data['data']['object_sub_type'][0] : '--' );

  // Antigo e novo resposáveis do equipamento
  $old_applicant_name = ( get_userdata( $old_applicant ) ? get_userdata( $old_applicant )->display_name : '--' );
  $new_applicant_name = ( get_userdata( $new_applicant ) ? get_userdata( $new_applicant )->display_name : '--' );

  // Local do equipamento
  $old_place_desc = '--';
  if ( ! empty( $old_place ) ) {
    $place = intranet_fafar_api_get_submission_by_id( $old_place );
    if ( ! empty( $place['data'] ) ) {
      $old_place_desc = ( ! empty( $place['data']['desc'] ) ? $place['data']['number'] . ' ' . $place['data']['desc'] : $place['data']['number'] );
    }
  }

  $new_place_desc = '--';
  if ( ! empty( $new_place ) ) {
    $place = intranet_fafar_api_get_submission_by_id( $new_place );
    if ( ! empty( $place['data'] ) ) {
      $new_place_desc = ( ! empty( $place['data']['desc'] ) ? $place['data']['number'] . ' ' . $place['data']['desc'] : $place['data']['number'] );
    }
  }
  
  $message = '
    <p>Houve mudança de <strong>responsável</strong> e/ou <strong>local</strong> no equipamento a seguir.</p>
    <p>
      O equipamento, de tipo <strong>' . $equipament_type . '</strong> e patrimônio <strong>' . $form_data['data']['asset'] . '</strong>, sofreu alterações.
    </p>
    ' . 
    (
      $HAS_APPLICANT_CHANGED === true ? 
      '<p> Responsabilidade: De <strong>' . $old_applicant_name . '</strong> para <strong>' . $new_applicant_name . '</strong>. </p>' : 
      ''
    ) 
    . 
    (
      $HAS_PLACE_CHANGED === true ? 
      '<p> Sala: De <strong>' . $old_place_desc . '</strong> para <strong>' . $new_place_desc . '</strong>. </p>' : 
      ''
    ) 
     . '
    <p>Caso precise de mais alguma informação, não hesite em nos contatar.</p>
  ';

  $subject = 'Mudança em equipamento';
  $to      = 'spatri@farmacia.ufmg.br';

  intranet_fafar_mail_notify( $to, $subject, $message );

  $form_data['data'] = json_encode( $form_data['data'] );

  return $form_data;
}

function intranet_fafar_mail_on_create_access_building_request( $form_data ) {
  if( ! isset( $form_data['object_name'] ) ) return $form_data;

  if ( $form_data['object_name'] !== 'access_building_request' ) return $form_data;

  $form_data['data'] = json_decode( $form_data['data'], true );

  // Responsável do pedido de acesso
  $user_info   = get_userdata( $form_data['owner'] );
  $owner_name  = $user_info ? $user_info->display_name : '';
  $owner_email = $user_info ? $user_info->user_email : '';

  // Local do pedido de acesso
  $place_desc = '';
  if ( ! empty( $form_data['data']['place'][0] ) ) {
    $place = intranet_fafar_api_get_submission_by_id( $form_data['data']['place'][0] );

    if ( ! empty( $place['data'] ) ) {
      $place_desc = $place['data']['number'] . ( ! empty( $place['data']['desc'] ) ? ' ' . $place['data']['desc'] : '' );
    }
  }
  
  $message = '
    <p>Sua solicitação de acesso ao prédio foi aprovada!</p>
    <p><strong>Detalhes do pedido:</strong></p>
    <ul>
      <li><strong>Tipo:</strong> ' . $form_data['data']['access_building_request_type'][0] . '</li>
      <li><strong>Terceiro:</strong> ' . $form_data['data']['third_party_name'] . '</li>
      <li><strong>Período:</strong> ' . intranet_fafar_utils_format_date( $form_data['data']['start_date'] ) . ' - ' . intranet_fafar_utils_format_date( $form_data['data']['end_date'] ) . '</li>
      <li><strong>Local:</strong> ' . $place_desc . ( $form_data['data']['lab'] ? ' - ' . $form_data['data']['lab'] : '' ) . '</li>
      <li><strong>Justificativa:</strong> ' . $form_data['data']['justification_for_request'] . '</li>
    </ul>
    <p>Caso precise de mais alguma informação, não hesite em nos contatar.</p>
  ';

  $subject = '🎉 Seu Acesso ao Prédio Foi Aprovado';

  intranet_fafar_mail_notify( $owner_email, $subject, $message );

  $form_data['data'] = json_encode( $form_data['data'] );

  return $form_data;
}

function intranet_fafar_mail_on_change_auditorium_reservation_status( $reservation ) {
  if ( empty( $reservation ) ) return false;
  
  $message = '
    <p>Informamos que o status da sua reserva do dia ' . intranet_fafar_utils_format_date( $reservation['data']['event_date'] ) . ', no auditório Aluísio Pimenta, foi atualizado.</p>
    <p><strong>Detalhes da Reserva:</strong></p>
    <ul>
      <li><strong>Evento:</strong> ' . $reservation['data']['desc'] . '</li>
      <li><strong>Status:</strong> <mark> ' . $reservation['data']['status'] . ' </mark></li>
      <li><strong>Dia:</strong> ' . intranet_fafar_utils_format_date( $reservation['data']['event_date'] ) . '</li>
      <li><strong>Horário:</strong> ' . $reservation['data']['start_time'] . ' - ' . $reservation['data']['end_time'] . '</li>
    </ul>
  ';

  $subject = 'Atualização no Status da Sua Reserva de Auditório 🔄';
  $to      = $reservation['data']['applicant_email'];

  error_log(print_r($to,true));
  error_log(print_r($subject,true));
  error_log(print_r($message,true));

  intranet_fafar_mail_notify( $to, $subject, $message );

  return true;
}

function intranet_fafar_mail_on_set_auditorium_reservation_technical( $reservation ) {
  if ( empty( $reservation ) ) return false;

  if ( empty( $reservation['data']['technical'] ) ) return false;

  // Responsável do equipamento
  $user_info      = get_userdata( $reservation['data']['technical'] );
  $technical_name = $user_info ? $user_info->display_name : '';

  $applicant_name = $reservation['data']['applicant_name'];
  
  $message = '
    <p>😊 Informamos que o técnico ' . $technical_name . ' foi designado para acompanhar o seu evento e garantir que tudo funcione perfeitamente.</p>
    <p><strong>Detalhes da Reserva:</strong></p>
    <ul>
      <li><strong>Evento:</strong> ' . $reservation['data']['desc'] . '</li>
      <li><strong>Status:</strong> ' . $reservation['data']['status'] . '</li>
      <li><strong>Dia:</strong> ' . intranet_fafar_utils_format_date( $reservation['data']['event_date'] ) . '</li>
      <li><strong>Horário:</strong> ' . $reservation['data']['start_time'] . ' - ' . $reservation['data']['end_time'] . '</li>
      <li><strong>Técnico Responsável:</strong> ' . $technical_name . '</li>
      <li><strong>Contato:</strong> <a href="tel:(31) 3409-6751">(31) 3409-6751</a></li>
    </ul>
    <p>Caso precise de mais alguma informação, não hesite em nos contatar.</p>
  ';

  $subject = 'Técnico Designado para Sua Reserva de Auditório 🛠️';
  $to      = $reservation['data']['applicant_email'];

  intranet_fafar_mail_notify( $to, $subject, $message );

  return true;
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

    if ( defined( 'WP_DEV_ENV' ) && WP_DEV_ENV === true ) {
      $to      = 'suporte@farmacia.ufmg.br';
      $subject = '[FAFAR DEV ENV] ' . $subject;
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