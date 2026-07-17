<?php

defined('BASEPATH') || exit('No direct script access allowed');

// connect account
$lang['whatsbot'] = 'WhatsBot';
$lang['connect_account'] = 'Konto verbinden';
$lang['connect_whatsapp_business'] = 'WhatsApp Business verbinden';
$lang['campaigning'] = 'Kampagnen';
$lang['business_account_id_description'] = 'Ihre WhatsApp Business Account (WABA) ID';
$lang['access_token_description'] = 'Ihr Benutzerzugangstoken nach der Registrierung bei Facebook Developers Portal';
$lang['whatsapp_business_account_id'] = 'WhatsApp Business Account ID';
$lang['whatsapp_access_token'] = 'WhatsApp Zugangstoken';
$lang['webhook_callback_url'] = 'Webhook Callback URL';
$lang['verify_token'] = 'Token überprüfen';
$lang['connect'] = 'Verbinden';
$lang['whatsapp'] = 'WhatsApp';
$lang['one_click_account_connection'] = 'Ein-Klick-Kontoverbindung';
$lang['connect_your_whatsapp_account'] = 'Verbinden Sie Ihr WhatsApp-Konto';
$lang['copy'] = 'Kopieren';
$lang['copied'] = 'Kopiert!!';
$lang['disconnect'] = 'Trennen';
$lang['number'] = 'Nummer';
$lang['number_id'] = 'Nummer ID';
$lang['quality'] = 'Qualität';
$lang['status'] = 'Status';
$lang['business_account_id'] = 'Business Account ID';
$lang['permissions'] = 'Berechtigungen';
$lang['phone_number_id_description'] = 'ID der Telefonnummer, die mit der WhatsApp Business API verbunden ist. Wenn Sie sich nicht sicher sind, können Sie eine GET Phone Number ID-Anfrage verwenden, um sie von der WhatsApp API abzurufen (https://developers.facebook.com/docs/whatsapp/business-management-api/manage-phone-numbers)';
$lang['phone_number_id'] = 'Nummer ID der bei WhatsApp registrierten Telefonnummer';
$lang['update_details'] = 'Details aktualisieren';

$lang['bots'] = 'Bots';
$lang['bots_management'] = 'Bot-Verwaltung';
$lang['create_template_base_bot'] = 'Vorlagen-Bot erstellen';
$lang['create_message_bot'] = 'Nachrichten-Bot erstellen';
$lang['type'] = 'Typ';
$lang['message_bot'] = 'Nachrichten-Bot';
$lang['new_template_bot'] = 'Neuer Vorlagen-Bot';
$lang['new_message_bot'] = 'Neuer Nachrichten-Bot';
$lang['bot_name'] = 'Bot-Name';
$lang['reply_text'] = 'Antworttext <i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="Text, der an den Lead oder Kontakt gesendet wird. Sie können auch {companyname}, {crm_url} oder andere benutzerdefinierte Merge-Felder des Leads oder Kontakts verwenden oder das \'@\'-Zeichen verwenden, um verfügbare Merge-Felder zu finden." data-placement="bottom"></i> <span class="text-muted">(Maximale zulässige Zeichen sollten 1024 sein)</span>';
$lang['reply_type'] = 'Antworttyp';
$lang['trigger'] = 'Trigger';
$lang['header'] = 'Header';
$lang['footer_bot'] = 'Footer <i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="Maximale zulässige Zeichen sollten 60 sein" data-placement="bottom"></i>';
$lang['bot_with_reply_buttons'] = 'Option 1: Bot mit Antwortschaltflächen';
$lang['bot_with_button_link'] = 'Option 2: Bot mit Schaltflächenlink - CTA-URL';
$lang['button1'] = 'Schaltfläche 1 <i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="Maximale zulässige Zeichen sollten 20 sein" data-placement="bottom"></i>';
$lang['button1_id'] = 'Schaltfläche 1 ID <i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="Maximale zulässige Zeichen sollten 256 sein" data-placement="bottom"></i>';
$lang['button2'] = 'Schaltfläche 2 <i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="Maximale zulässige Zeichen sollten 20 sein" data-placement="bottom"></i>';
$lang['button2_id'] = 'Schaltfläche 2 ID <i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="Maximale zulässige Zeichen sollten 256 sein" data-placement="bottom"></i>';
$lang['button3'] = 'Schaltfläche 3 <i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="Maximale zulässige Zeichen sollten 20 sein" data-placement="bottom"></i>';
$lang['button3_id'] = 'Schaltfläche 3 ID <i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="Maximale zulässige Zeichen sollten 256 sein" data-placement="bottom"></i>';
$lang['button_name'] = 'Schaltflächenname <i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="Maximale zulässige Zeichen sollten 20 sein" data-placement="bottom"></i>';
$lang['button_link'] = 'Schaltflächenlink';
$lang['enter_name'] = 'Name eingeben';
$lang['select_reply_type'] = 'Antworttyp auswählen';
$lang['enter_bot_reply_trigger'] = 'Trigger für Bot-Antwort eingeben';
$lang['enter_header'] = 'Header eingeben';
$lang['enter_footer'] = 'Footer eingeben';
$lang['enter_button1'] = 'Schaltfläche 1 eingeben';
$lang['enter_button1_id'] = 'ID für Schaltfläche 1 eingeben';
$lang['enter_button2'] = 'Schaltfläche 2 eingeben';
$lang['enter_button2_id'] = 'ID für Schaltfläche 2 eingeben';
$lang['enter_button3'] = 'Schaltfläche 3 eingeben';
$lang['enter_button3_id'] = 'ID für Schaltfläche 3 eingeben';
$lang['enter_button_name'] = 'Schaltflächenname eingeben';
$lang['enter_button_url'] = 'Schaltflächen-URL eingeben';
$lang['on_exact_match'] = 'Antwortbot: Bei exaktem Match';
$lang['when_message_contains'] = 'Antwortbot: Wenn die Nachricht enthalten ist';
$lang['when_client_send_the_first_message'] = 'Willkommensantwort - wenn Lead oder Kunde die erste Nachricht sendet';
$lang['bot_create_successfully'] = 'Bot erfolgreich erstellt';
$lang['bot_update_successfully'] = 'Bot erfolgreich aktualisiert';
$lang['bot_deleted_successfully'] = 'Bot erfolgreich gelöscht';
$lang['templates'] = 'Vorlagen';
$lang['template_data_loaded'] = 'Vorlagen erfolgreich geladen';
$lang['load_templates'] = 'Vorlagen laden';
$lang['template_management'] = 'Vorlagenverwaltung';

// campaigns
$lang['campaign'] = 'Kampagne';
$lang['campaigns'] = 'Kampagnen';
$lang['send_new_campaign'] = 'Neue Kampagne senden';
$lang['campaign_name'] = 'Kampagnenname';
$lang['template'] = 'Vorlage';
$lang['scheduled_send_time'] = '<i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="Pro Kunde, basierend auf der Zeitzone des Kontakts" data-placement="left"></i>Geplanter Sendetermin';
$lang['scheduled_time_description'] = 'Pro Kunde, basierend auf der Zeitzone des Kontakts';
$lang['ignore_scheduled_time_and_send_now'] = 'Geplante Zeit ignorieren und jetzt senden';
$lang['template'] = 'Vorlage';
$lang['leads'] = 'Leads';
$lang['delivered_to'] = 'Zugestellt an';
$lang['read_by'] = 'Gelesen von';
$lang['variables'] = 'Variablen';
$lang['body'] = 'Nachricht';
$lang['variable'] = 'Variable';
$lang['match_with_selected_field'] = 'Mit einem ausgewählten Feld übereinstimmen';
$lang['preview'] = 'Vorschau';
$lang['send_campaign'] = 'Kampagne senden';
$lang['send_to'] = 'Senden an';
$lang['send_campaign'] = 'Kampagne senden';
$lang['view_campaign'] = 'Kampagne ansehen';
$lang['campaign_daily_task'] = 'Tägliche Kampagnenaufgabe';
$lang['back'] = 'Zurück';
$lang['phone'] = 'Telefon';
$lang['message'] = 'Nachricht';
$lang['currently_type_not_supported'] = 'Derzeit wird der Typ <strong> %s </strong> Vorlage nicht unterstützt!';
$lang['of_your'] = 'von Ihren ';
$lang['contacts'] = 'Kontakten';
$lang['select_all_leads'] = 'Alle Leads auswählen';
$lang['select_all_note_leads'] = 'Wenn Sie dies auswählen, werden alle zukünftigen Leads in diese Kampagne einbezogen.';
$lang['select_all_note_contacts'] = 'Wenn Sie dies auswählen, werden alle zukünftigen Kontakte in diese Kampagne einbezogen.';

$lang['verified_name'] = 'Verifizierter Name';
$lang['mark_as_default'] = 'Als Standard markieren';
$lang['default_number_updated'] = 'Standardtelefonnummer erfolgreich aktualisiert';
$lang['currently_using_this_number'] = 'Derzeit wird diese Nummer verwendet';
$lang['leads'] = 'Leads';
$lang['pause_campaign'] = 'Kampagne pausieren';
$lang['resume_campaign'] = 'Kampagne fortsetzen';
$lang['campaign_resumed'] = 'Kampagne fortgesetzt';
$lang['campaign_paused'] = 'Kampagne pausiert';

//Template
$lang['body_data'] = 'Nachrichtendaten';
$lang['category'] = 'Kategorie';

// Template bot
$lang['create_new_template_bot'] = 'Neuen Vorlagenbot erstellen';
$lang['template_bot'] = 'Vorlagenbot';
$lang['variables'] = 'Variablen';
$lang['preview'] = 'Vorschau';
$lang['template'] = 'Vorlage';
$lang['bot_content_1'] = 'Diese Nachricht wird an den Kontakt gesendet, sobald die Triggerregel in der Nachricht erfüllt ist, die vom Kontakt gesendet wurde.';
$lang['save_bot'] = 'Bot speichern';
$lang['please_select_template'] = 'Bitte wählen Sie eine Vorlage aus';
$lang['use_manually_define_value'] = 'Manuell definierten Wert verwenden';
$lang['merge_fields'] = 'Felder zusammenführen';
$lang['template_bot_create_successfully'] = 'Vorlagenbot erfolgreich erstellt';
$lang['template_bot_update_successfully'] = 'Vorlagenbot erfolgreich aktualisiert';
$lang['text_bot'] = 'Textbot';
$lang['option_2_bot_with_link'] = 'Option 2: Bot mit Schaltflächenlink - Handlungsaufforderung (CTA) URL';
$lang['option_3_file'] = 'Option 3: Bot mit Datei';
// Bot settings
$lang['bot'] = 'Bot';
$lang['bot_delay_response'] = 'Nachricht senden, wenn eine Verzögerung in der Antwort erwartet wird';
$lang['bot_delay_response_placeholder'] = 'Gib mir einen Moment, ich werde gleich die Antwort haben';

$lang['whatsbot'] = 'WhatsBot';

//campaigns
$lang['relation_type'] = 'Beziehungstyp';
$lang['select_all'] = 'Alle auswählen';
$lang['total'] = 'Gesamt';
$lang['merge_field_note'] = 'Verwenden Sie das Zeichen \'@\' zum Hinzufügen von zusammenzuführenden Feldern.';
$lang['send_to_all'] = 'An alle senden';
$lang['or'] = 'ODER';

$lang['convert_whatsapp_message_to_lead'] = 'Neuen Lead automatisch erfassen (neue WhatsApp-Nachrichten in Leads umwandeln)';
$lang['leads_status'] = 'Lead-Status';
$lang['leads_assigned'] = 'Lead zugewiesen';
$lang['whatsapp_auto_lead'] = 'WhatsApp Auto Lead';
$lang['webhooks_label'] = 'Die empfangenen WhatsApp-Daten werden erneut gesendet an';
$lang['webhooks'] = 'WebHooks';
$lang['enable_webhooks'] = 'WebHooks erneut senden aktivieren';
$lang['chat'] = 'Chat';
$lang['black_listed_phone_numbers'] = 'Schwarze Liste von Telefonnummern';
$lang['sent_status'] = 'Gesendet Status';

$lang['active'] = 'Aktiv';
$lang['approved'] = 'Genehmigt';
$lang['this_month'] = 'diesen Monat';
$lang['open_chats'] = 'Offene Chats';
$lang['resolved_conversations'] = 'Gelöste Konversationen';
$lang['messages_sent'] = 'Gesendete Nachrichten';
$lang['account_connected'] = 'Konto verbunden';
$lang['account_disconnected'] = 'Konto getrennt';
$lang['webhook_verify_token'] = 'Webhook-Bestätigungstoken';
// Chat integration
$lang['chat_message_note'] = 'Nachricht wird in Kürze gesendet. Bitte beachten Sie, dass neue Kontakte in dieser Liste nicht angezeigt werden, bis der Kontakt mit Ihnen interagiert!';

$lang['activity_log'] = 'Aktivitätsprotokoll';
$lang['whatsapp_logs'] = 'WhatsApp-Protokolle';
$lang['response_code'] = 'Antwortcode';
$lang['recorded_on'] = 'Aufgezeichnet am';

$lang['request_details'] = 'Anforderungsdetails';
$lang['raw_content'] = 'Rohinhalt';
$lang['headers'] = 'Header';
$lang['format_type'] = 'Formattyp';

// Permission section
$lang['show_campaign'] = 'Kampagne anzeigen';
$lang['clear_log'] = 'Protokoll löschen';
$lang['log_activity'] = 'Aktivität protokollieren';
$lang['load_template'] = 'Vorlage laden';

$lang['action'] = 'Aktion';
$lang['total_parameters'] = 'Gesamtparameter';
$lang['template_name'] = 'Vorlagenname';
$lang['log_cleared_successfully'] = 'Protokoll erfolgreich gelöscht';
$lang['whatsbot_stats'] = 'WhatsBot Statistiken';

$lang['not_found_or_deleted'] = 'Nicht gefunden oder gelöscht';
$lang['response'] = 'Antwort';

$lang['select_image'] = 'Bild auswählen';
$lang['image'] = 'Bild';
$lang['image_deleted_successfully'] = 'Bild erfolgreich gelöscht';
$lang['whatsbot_settings'] = 'Whatsbot Einstellungen';
$lang['maximum_file_size_should_be'] = 'Die maximale Dateigröße sollte sein ';
$lang['allowed_file_types'] = 'Erlaubte Dateitypen: ';

$lang['send_image'] = 'Bild senden';
$lang['send_video'] = 'Video senden';
$lang['send_document'] = 'Dokument senden';
$lang['record_audio'] = 'Audio aufnehmen';
$lang['chat_media_info'] = 'Weitere Informationen zu unterstützten Inhaltsarten und zur Nachbearbeitung der Medienneigung';
$lang['help'] = 'Hilfe';

// v1.1.0
$lang['clone'] = 'Klonen';
$lang['bot_clone_successfully'] = 'Bot erfolgreich geklont';
$lang['all_chat'] = 'Alle Chats';
$lang['from'] = 'Von:';
$lang['phone_no'] = 'Telefonnummer:';
$lang['supportagent'] = 'Support-Agent';
$lang['assign_chat_permission_to_support_agent'] = 'Chat-Berechtigung nur dem Support-Agenten zuweisen';
$lang['enable_whatsapp_notification_sound'] = 'WhatsApp-Benachrichtigungston aktivieren';
$lang['notification_sound'] = 'Benachrichtigungston';
$lang['trigger_keyword'] = 'Schlüsselwort auslösen';
$lang['modal_title'] = 'Support-Agent auswählen';
$lang['close_btn'] = 'Schließen';
$lang['save_btn'] = 'Speichern';
$lang['support_agent'] = 'Support-Agent';
$lang['change_support_agent'] = 'Support-Agent ändern';
$lang['replay_message'] = 'Sie können keine Nachricht senden, 24 Stunden sind vergangen.';
$lang['support_agent_note'] = '<strong>Hinweis:</strong> Wenn Sie die Support-Agenten-Funktion aktivieren, wird der Lead-Zuweisungsagent automatisch dem Chat zugewiesen. Administratoren können auch einen neuen Agenten auf der Chat-Seite zuweisen.';
$lang['permission_bot_clone'] = 'Bot klonen';
$lang['remove_chat'] = 'Chat entfernen';
$lang['default_message_on_no_match'] = 'Standardantwort – wenn kein Schlüsselwort übereinstimmt';
$lang['default_message_note'] = '<strong>Hinweis:</strong> Wenn Sie diese Option aktivieren, wird Ihre Webhook-Belastung erhöht. Weitere Informationen finden Sie unter diesem <a href="https://docs.corbitaltech.dev/products/whatsbot/index.html" target="_blank">Link</a>.';

$lang['whatsbot_connect_account'] = 'WhatsBot-Konto verbunden';
$lang['whatsbot_message_bot'] = 'WhatsBot-Nachrichtenbot';
$lang['whatsbot_template_bot'] = 'WhatsBot-Vorlagenbot';
$lang['whatsbot_template'] = 'WhatsBot-Vorlage';
$lang['whatsbot_campaigns'] = 'WhatsBot-Kampagnen';
$lang['whatsbot_chat'] = 'WhatsBot-Chat';
$lang['whatsbot_log_activity'] = 'WhatsBot-Aktivitätsprotokoll';
$lang['message_templates_not_exists_note'] = 'Meta-Vorlagenberechtigung fehlt. Bitte aktivieren Sie es in Ihrem Meta-Konto.';

// v1.2.0
$lang['ai_prompt'] = 'KI-Eingaben';
$lang['ai_prompt_note'] = 'Für KI-Eingaben geben Sie bitte eine Nachricht ein, um die Funktion zu aktivieren, oder verwenden Sie KI-Eingaben, wenn sie bereits aktiviert sind.';
$lang['emojis'] = 'Emojis';
$lang['translate'] = 'Übersetzen';
$lang['change_tone'] = 'Ton ändern';
$lang['professional'] = 'Professionell';
$lang['friendly'] = 'Freundlich';
$lang['empathetic'] = 'Empathisch';
$lang['straightforward'] = 'Direkt';
$lang['simplify_language'] = 'Sprache vereinfachen';
$lang['fix_spelling_and_grammar'] = 'Rechtschreibung und Grammatik korrigieren';

$lang['ai_integration'] = 'KI-Integration';
$lang['open_ai_api'] = 'OpenAI-API';
$lang['open_ai_secret_key'] = 'OpenAI-Geheimschlüssel - <a href="https://platform.openai.com/account/api-keys" target="_blank">Wo finde ich den Geheimschlüssel?</a>';
$lang['chat_text_limit'] = 'Chat-Textlimit';
$lang['chat_text_limit_note'] = 'Um Betriebskosten zu optimieren, sollten Sie die Wortanzahl in den Chat-Antworten von OpenAI begrenzen.';
$lang['chat_model'] = 'Chat-Modell';
$lang['openai_organizations'] = 'OpenAI-Organisationen';
$lang['template_type'] = 'Vorlagentyp';
$lang['update'] = 'Aktualisieren';
$lang['open_ai_key_verification_fail'] = 'OpenAI-Schlüsselüberprüfung ist ausstehend in den Einstellungen, bitte verbinden Sie Ihr OpenAI-Konto';
$lang['enable_wb_openai'] = 'OpenAI im Chat aktivieren';
$lang['webhook_resend_method'] = 'Webhook-Neusendemethode';
$lang['search_language'] = 'Sprache suchen...';
$lang['document'] = 'Dokument';
$lang['select_document'] = 'Dokument auswählen';
$lang['attchment_deleted_successfully'] = 'Anhang erfolgreich gelöscht';
$lang['attach_image_video_docs'] = 'Bilder, Videos, Dokumente anhängen';
$lang['choose_file_type'] = 'Dateityp auswählen';
$lang['max_size'] = 'Maximale Größe: ';

// v1.3.0

// CSV import
$lang['bulk_campaigns'] = 'Massenkampagnen';
$lang['upload_csv'] = 'CSV hochladen';
$lang['upload'] = 'Hochladen';
$lang['csv_uploaded_successfully'] = 'CSV-Datei erfolgreich hochgeladen';
$lang['please_select_file'] = 'Bitte wählen Sie eine CSV-Datei aus';
$lang['phonenumber_field_is_required'] = 'Das Telefonnummernfeld ist erforderlich';
$lang['out_of_the'] = 'Von den';
$lang['records_in_your_csv_file'] = 'Einträgen in Ihrer CSV-Datei,';
$lang['valid_the_campaign_can_be_sent'] = 'Einträge sind gültig.<br />Die Kampagne kann erfolgreich an diese gesendet werden';
$lang['users'] = 'Benutzer';
$lang['campaigns_from_csv_file'] = 'Kampagnen aus der CSV-Datei';
$lang['download_sample'] = 'Beispiel herunterladen';
$lang['csv_rule_1'] = '1. <b>Erfordernis der Spalte Telefonnummer:</b> Ihre CSV-Datei muss eine Spalte mit dem Namen "Phoneno." enthalten. Jeder Eintrag in dieser Spalte muss eine gültige Kontaktnummer im richtigen Format mit Ländervorwahl und "+"-Zeichen enthalten.<br /><br />';
$lang['csv_rule_2'] = '2. <b>CSV-Format und -Codierung:</b> Ihre CSV-Daten müssen dem angegebenen Format folgen. Die erste Zeile Ihrer CSV-Datei muss die Spaltenüberschriften enthalten, wie im Beispiel dargestellt. Stellen Sie sicher, dass Ihre Datei in UTF-8 codiert ist, um Codierungsprobleme zu vermeiden.';
$lang['please_upload_valid_csv_file'] = 'Bitte laden Sie eine gültige CSV-Datei hoch';
$lang['please_add_valid_number_in_csv_file'] = 'Bitte fügen Sie eine gültige <b>Phoneno</b> in die CSV-Datei ein';
$lang['total_send_campaign_list'] = 'Gesamt gesendete Kampagnen: %s';
$lang['sample_data'] = 'Beispieldaten';
$lang['firstname'] = 'Vorname';
$lang['lastname'] = 'Nachname';
$lang['phoneno'] = 'Telefonnummer';
$lang['email'] = 'E-Mail';
$lang['country'] = 'Land';
$lang['download_sample_and_read_rules'] = 'Beispieldatei herunterladen & Regeln lesen';
$lang['please_wait_your_request_in_process'] = 'Bitte warten Sie, Ihre Anfrage wird bearbeitet.';
$lang['whatsbot_bulk_campaign'] = 'WhatsBot-Massenkampagnen';
$lang['csv_campaign'] = 'CSV-Kampagne';

// Canned reply
$lang['canned_reply'] = 'Vorgefertigte Antwort';
$lang['canned_reply_menu'] = 'Vorgefertigte Antwort';
$lang['create_canned_reply'] = 'Vorgefertigte Antwort erstellen';
$lang['title'] = 'Titel';
$lang['desc'] = 'Beschreibung';
$lang['public'] = 'Öffentlich';
$lang['action'] = 'Aktion';
$lang['delete_successfully'] = 'Antwort erfolgreich gelöscht.';
$lang['cannot_delete'] = 'Die Antwort kann nicht gelöscht werden.';
$lang['whatsbot_canned_reply'] = 'WhatsBot-Vorgefertigte Antwort';
$lang['reply'] = 'Antworten';

//AI Prompts
$lang['ai_prompts'] = 'KI-Eingaben';
$lang['create_ai_prompts'] = 'KI-Eingaben erstellen';
$lang['name'] = 'Name';
$lang['action'] = 'Aktion';
$lang['prompt_name'] = 'Eingabename';
$lang['prompt_action'] = 'Eingabeaktion';
$lang['whatsbot_ai_prompts'] = 'WhatsBot-KI-Eingaben';

// new chat
$lang['replying_to'] = 'Antwort auf:';
$lang['download_document'] = 'Dokument herunterladen';
$lang['custom_prompt'] = 'Benutzerdefinierte Eingabe';
$lang['canned_replies'] = 'Vorgefertigte Antworten';
$lang['use_@_to_add_merge_fields'] = 'Verwenden Sie \'@\', um Zusammenführungsfelder hinzuzufügen';
$lang['type_your_message'] = 'Geben Sie Ihre Nachricht ein';
$lang['you_cannot_send_a_message_using_this_number'] = 'Sie können keine Nachricht mit dieser Nummer senden.';

// bot flow
$lang['bot_flow'] = 'Bot-Flow';
$lang['create_new_flow'] = 'Neuen Flow erstellen';
$lang['flow_name'] = 'Flow-Name';
$lang['flow'] = 'Flow';
$lang['bot_flow_builder'] = 'Bot-Flow-Builder';
$lang['you_can_not_upload_file_type'] = 'Sie können den Dateityp <b> %s </b> nicht hochladen';
$lang['whatsbot_bot_flow'] = 'WhatsBot-Bot-Flow';

// v1.3.2
$lang['auto_clear_chat_history'] = 'Automatische Löschung des Chatverlaufs';
$lang['enable_auto_clear_chat_history'] = 'Automatische Löschung des Chatverlaufs aktivieren';
$lang['auto_clear_time'] = 'Zeit für automatische Löschung des Chatverlaufs';
$lang['clear_chat_history_note'] = '<strong>Hinweis:</strong> Wenn Sie die automatische Löschung des Chatverlaufs aktivieren, wird der Chatverlauf entsprechend der von Ihnen angegebenen Anzahl an Tagen jedes Mal gelöscht, wenn der Cron-Job ausgeführt wird.';
$lang['source'] = 'Quelle';
$lang['groups'] = 'Gruppen';

// v1.3.3
$lang['click_user_to_chat'] = 'Klicken Sie auf den Benutzer, um zu chatten';
$lang['searching'] = 'Suche...';
$lang['filters'] = 'Filter';
$lang['relation_type'] = 'Beziehungstyp';
$lang['groups'] = 'Gruppen';
$lang['source'] = 'Quelle';
$lang['status'] = 'Status';
$lang['select_type'] = 'Typ auswählen';
$lang['select_agents'] = 'Agenten auswählen';
$lang['select_group'] = 'Gruppe auswählen';
$lang['select_source'] = 'Quelle auswählen';
$lang['select_status'] = 'Status auswählen';
$lang['agents'] = 'Agenten';

// v1.4.2
$lang['read_only'] = 'Nur lesen';

// v2.0.0
$lang['personal_assistant'] = 'AI Personal Assistant';
$lang['create_personal_assistant'] = 'Create AI Personal Assistant';
$lang['assistant_name'] = 'Assistant name';
$lang['pa_files'] = 'Upload files for AI analysis';
$lang['new_personal_assistant'] = 'New Personal Assistant';
$lang['edit_personal_assistant'] = 'Edit Personal Assistant';

$lang['click_to_get_qr_code'] = 'Click to get QR Code';
$lang['phone_numbers'] = 'Phone Numbers';
$lang['display_phone_number'] = 'Display Phone Number';
$lang['update_business_profile'] = 'Update Business Profile';
$lang['resync_phone_numbers'] = 'Re-sync Phone Numbers';
$lang['manage_phone_numbers'] = 'Manage Phone Numbers';
$lang['scan_qr_code_to_start_chat'] = 'Scan QR Code to Start Chat';
$lang['use_qr_code_to_invite'] = 'You can use the following QR Codes to invite people on this platform.';
$lang['url_for_qr_image'] = 'URL for QR Image';
$lang['whatsapp_url'] = 'WhatsApp URL';
$lang['whatsapp_now'] = 'WhatsApp Now';

$lang['file_upload_guidelines'] = 'File upload guidelines for best results';
$lang['supported_file_formats'] = 'Supported file formats';
$lang['pdf'] = 'PDF';
$lang['pdf_text'] = ': Only text-based PDFs (not scanned images).';
$lang['word'] = 'Word (DOC/DOCX)';
$lang['word_text'] = ': Text-based documents only (avoid images or scanned content).';
$lang['text'] = 'Text (TXT)';
$lang['text_text'] = ': Simple, plain text files with UTF-8 encoding.';
$lang['what_to_avoid'] = 'What to avoid';
$lang['scanned_images'] = 'Scanned Images';
$lang['scanned_images_text'] = ': Ensure documents are not image-based. Use OCR software for scanned PDFs.';
$lang['junk_characters'] = 'Junk Characters';
$lang['junk_characters_text'] = ': Avoid non-standard or corrupted characters in the document.';
$lang['large_files'] = 'Large Files';
$lang['large_files_text'] = ': Keep the file size reasonable for optimal performance.';
$lang['file_naming'] = 'File naming';
$lang['avoid_special_characters'] = 'Avoid Special Characters';
$lang['avoid_special_characters_text'] = ': Use alphanumeric characters and underscores in filenames (e.g., document_name.pdf).';
$lang['best_practices'] = 'Best practices';
$lang['well_structured_text'] = 'Use well-structured text with clear headings and paragraphs.';
$lang['proper_encoding'] = 'Ensure proper encoding (UTF-8) for text files.';
$lang['modal_processing_note'] = 'We are processing your document...';
$lang['incorrect_api_key_provided'] = 'Incorrect API key provided';
$lang['phone_number'] = 'Phone Number';
$lang['overall_health'] = 'Overall Health';
$lang['whatsApp_business_id'] = 'WhatsApp Business ID';
$lang['status_as_at'] = 'Status as at';
$lang['fb_app_id'] = 'Facebook App ID <a href="https://developers.facebook.com/docs/whatsapp/solution-providers/get-started-for-tech-providers#step-2--create-a-meta-app" class="mleft10 text-danger" target="_blank">HELP</a>';
$lang['fb_app_secret'] = 'Facebook App Secret';
$lang['facebook_developer_account_facebook_app'] = 'Step - 1 : Facebook Developer Account & Facebook App';
$lang['access_token_information'] = 'Access Token Information';
$lang['debug_token'] = 'Debug token';
$lang['permission_scopes'] = 'Permission scopes';
$lang['expiry_at'] = 'Expiry at';
$lang['disconnect_webhook'] = 'Disconnect Webhook';
$lang['webhook_configured'] = 'Webhook Configured';
$lang['connect_webhook'] = 'Connect Webhook';
$lang['webhook_subscribed_successfully'] = 'Webhook subscribe successfully';
$lang['can_send_message'] = 'Can Send Messages';
$lang['overall_health_send_message'] = 'Overall Health of Send Messages';
$lang['refresh_status'] = 'Refresh status';
$lang['issued_at'] = 'Issued at';
$lang['connect_whatsapp_business_account'] = 'Connect Whatsapp Business Account';
$lang['whatsApp_integration_setup'] = 'Step - 2 : WhatsApp Integration Setup';
$lang['openai_key_not_verified_note'] = 'This feature requires OpenAI key verification, which is currently pending. Please <a href="' . admin_url("settings?group=whatsbot&tab=ai_integration") . '" class="alert-link">Click here</a> to verify your API key.';
$lang['cant_upload_file_verification_pending'] = 'You can\'t upload file OpenAI KEY verification pending';

$lang['ai_assistant'] = 'AI Assistant';
$lang['enable_ai_assistant'] = 'Enable AI Assistant';
$lang['stop_ai_assistant'] = 'Keyword to stop AI Assistant';
$lang['temperature'] = 'Temperature:';
$lang['max_token'] = 'Max Token:';
$lang['ai_model'] = 'AI Model';
$lang['temperature_note'] = 'Adjusts the randomness of the models responses. Lower values make outputs more focused and predictable while higher values make them more creative and diverse.';
$lang['max_tokens_note'] = 'Sets the maximum number of tokens the model can generate. This includes both input and output tokens. A higher value allows for longer responses but may use more processing time.';
$lang['bot_with_reply_buttons'] = 'Option 2: Bot with reply buttons';
$lang['option_2_bot_with_link'] = 'Option 3: Bot with button link - Call to Action (CTA) URL';
$lang['option_3_file'] = 'Option 4: Bot with file';
$lang['option_1_personal_assitant'] = 'Option 1: Personal AI assistant';
$lang['whatsbot_pa'] = 'Whatsbot Personal Assistant';


$lang['disconnect_acount'] = 'Disconnect Account';
$lang['whatsapp_business_account'] = 'WhatsApp business account ';
$lang['access_token_information'] = 'Access Token Information';
$lang['refresh_health_status'] = 'Refresh health status';
$lang['facebook_developer_account_info'] = 'Facebook developer account information';
$lang['fb_config_id'] = 'Facebook Config ID <a href="https://developers.facebook.com/docs/whatsapp/embedded-signup/implementation#step-2--create-facebook-login-for-business-configuration" class="mleft10 text-danger" target="_blank">HELP</a>';
$lang['connect_with_facebook'] = 'Connect with Facebook';
$lang['send_test_message'] = 'Send Test Message';
$lang['test_number_note'] = 'Add `+` and the country code before the number (e.g., `+15551234567`) to send a WhatsApp message.';
$lang['wb_number'] = 'WhatsApp number';
$lang['send_message'] = 'Send message';
$lang['message_sent_successfully'] = 'Message sent sucessfully';
$lang['configure'] = 'Configure';
$lang['enable_embaded_signin'] = 'Enable embedded SignIn';
$lang['save_details'] = 'Save details';
$lang['modal_processing_connect_account_note'] = 'We are procesing on your request';
$lang['user_cancle_note'] = 'User cancelled login or did not fully authorize.';
$lang['access_token'] = 'Access token';
$lang['webhook_url'] = 'Webhook URL';
$lang['please_enter_all_details'] = 'Please enter all details';
$lang['please_select_default_number_first'] = 'Please select a default WhatsApp number to send the test message';
$lang['please_add_number_for_send_message'] = 'Please add a WhatsApp number to send the test message';
$lang['webhook_connected'] = 'Webhook connected successfully';

// Update language line : Start
$lang['update_version'] = 'Update Version';
$lang['update_warning'] = 'Before performing an update, it is <b>strongly recommended to create a full backup</b> of your current installation <b>(files and database)</b> and review the changelog.';
$lang['upgrade_function'] = 'Upgrade Function';
$lang['download_files'] = 'Download files';
$lang['fix_errors'] = 'Please fix the errors listed below.';
$lang['module_update'] = 'Module Update';
$lang['username'] = 'Username';
$lang['changelog'] = 'Change Log';
$lang['check_update'] = 'Check Update';
$lang['database_upgrade_required'] = 'Database upgrade is required!';
$lang['update_content_1'] = 'You need to perform a database upgrade before proceeding. Your ';
$lang['update_content_2'] = '<strong>files version</strong> is ';
$lang['update_content_3'] = ' and <strong>database version</strong> is ';
$lang['update_content_4'] = 'Make sure that you have a backup of your database before performing an upgrade.';
$lang['update_content_5'] = 'This message may show if you uploaded files from a newer version downloaded from CodeCanyon to your existing installation or you used an auto-upgrade tool.';
$lang['upgrade_now'] = 'UPGRADE NOW';
$lang['module_updated_successfully'] = 'Module Updated Successfully';
$lang['create_support_ticket'] = 'Create Support Ticket';
$lang['support_ticket_content'] = 'Do you want custom services? Visit here and create your ticket';
// Update language line: Over

$lang['verify_webhook'] = 'Verify Webhook';
$lang['webhook_received_successfully'] = 'Webhook received successfully';
$lang['sending'] = 'Sending ...';
$lang['verify'] = 'Verify';

$lang['flows'] = "Flows";
$lang['flow_data_loaded'] = 'Flows loaded successfully';
$lang['load_flows'] = 'Load Flows';
$lang['flow_management'] = 'Flow Management';
$lang['flow_templates'] = 'Flow Templates';
$lang['flow_responses'] = 'Flow Responses';
$lang['receiver'] = 'Receiver';
$lang['submit_time'] = 'Submit time';
$lang['whatsapp_no'] = 'Whatsapp number';

$lang['marketing_automation'] = "Marketing Automation";
$lang['after_ticket_status_changed'] = 'Ticket Status Changed';
$lang['project_status_changed'] = 'Project Status Changed';
$lang['add'] = 'Add';
$lang['automation'] = 'Automation';

$lang['after_ticket_status_changed_subtext'] = 'Send the selected WhatsApp flow to the client when the ticket status changes.';
$lang['project_status_changed_subtext'] = 'Send the selected WhatsApp flow to the client when project status changes';

$lang['sender_phone'] = 'Phone number use for message sending';

$lang['section'] = 'Section';
$lang['text'] = 'Text';
$lang['sub_text'] = 'Sub Text';
$lang['submit_button_label'] = 'Submit Button Label';
$lang['option_5_bot_with_options'] = 'Option 5: Bot With Options';
$lang['choose_options'] = 'Choose Option';

$lang['flow_response'] = 'Flow Response';
$lang['not_allowed_to_view'] = 'Not allowed to view';

$lang['send_location'] = 'Send Location';
$lang['search_for_a_place'] = 'Search for a place';
$lang['address_optional'] = 'Address (optional)';
$lang['latitude'] = 'Latitude';
$lang['longitude'] = 'Longitude';
$lang['drag_the_maker_to_set_the_location'] = 'Drag the marker to set the location or search for a place.';
$lang['use_my_location'] = 'Use My Current Location';
$lang['send_location'] = 'Send Location';

$lang['initiate_chat'] = 'Initiate Chat';
$lang['please_select_at_least_one_lead'] = 'Please select at least one lead';
$lang['chat_initiated_successfully'] = 'Chat Initiated  successfuly';
$lang['something_went_wrong'] = "Something Went Wrong";

$lang['video'] = "Video";
$lang['select_video'] = "Select Video";

$lang['whatsbot_cron'] = 'Whatsbot Cron';

// Session Management
$lang['session_management']                 = 'WhatsApp Session Management';
$lang['enable_session_management']          = 'Enable session management';
$lang['session_management_help_text']       = 'WhatsApp Cloud API has a 24-hour session limit for business-initiated messages. Enable this to send a reminder before the session expires.';
$lang['session_expiry_message']             = 'Session expiry message';
$lang['session_expiry_message_help']        = 'This message will be sent to customers before their 24-hour session expires.';
$lang['session_expiry_hours']               = 'Hours before expiry to send reminder';
$lang['session_expiry_hours_help']          = 'Set how many hours after the last customer message to send the reminder (max 23 hours).';
$lang['include_session_reset_button']       = 'Include quick reply button';
$lang['include_session_reset_button_help']  = 'Add a quick reply button to make it easier for customers to respond.';
$lang['session_management_note']            = 'Note: This feature requires the cron job to be properly configured. The cron job should run at least once per hour.';
$lang['session_reset_button_text']          = 'Continue Conversation';
$lang['whatsapp_session_management']        = 'WhatsApp Session Management';
$lang['active_sessions']                    = 'Active Sessions';
$lang['expiring_sessions']                  = 'Expiring Sessions';
$lang['sessions_reset_today']               = 'Sessions Reset Today';
$lang['sessions_reset_week']                = 'Sessions Reset (7 Days)';
$lang['session_management_disabled']        = 'WhatsApp Session Management is currently disabled.';

// Dashboard and Reports
$lang['session_reset_count']                = 'Session Reset Count';
$lang['session_expired']                    = 'Session Expired';
$lang['session_active']                     = 'Session Active';
$lang['session_reset_success_rate']         = 'Reset Success Rate';
$lang['customer_responded']                 = 'Customer Responded';
$lang['customer_not_responded']             = 'Customer Did Not Respond';
$lang['enable_now']                         = 'Enable Now';
