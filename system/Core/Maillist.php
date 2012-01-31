<?php
/**
 * Почтовые рассылки.
 * 
 * @author	Artem Ryzhkov
 * @package	Kernel
 * @license	http://opensource.org/licenses/gpl-2.0
 * 
 * @uses	DB
 * @uses	Env
 * 
 * @version 2011-12-27.0
 */
class Maillist extends Base
{
	/**
	 * Кол-во писем которое будет отправлено при каждой итерации.
	 */
	protected $count_limit = 10;
	
	/**
	 * Добавить задачу на рассылку
	 *
	 * @param string $from - поле "от", может быть просто email или развернутый формат в виде "Имя <mail@mail.com>".
	 * @param string $subject - поле "тема".
	 * @param text $content - тело письма.
	 * @param array $emails - список емаилов на которые надо разослать письмо.
	 * @param int $priority - приоритет в минутах
	 * @param bool $is_archived - сохранять историю?
	 * 
	 * @todo можно оптимизировать добавление емаилов в виде расширенной вставки, чтобы за один SQL запрос добавлять сразу несколько емаилов.
	 */
	public function add($from, $subject, $content, array $emails, $priority = 5, $is_archived = 0)
	{
		$from		= $this->DB->quote($from);
		$subject	= $this->DB->quote($subject);
		$content	= $this->DB->quote($content);
		$length		= strlen($content);
		$emails_count = count($emails);
		
		$sql = "
			INSERT INTO {$this->DB->prefix()}maillist
				(site_id, is_archived, email_from, subject, content, length, datetime, priority, emails_count)
			VALUES
				('{$this->Env->site_id}', $is_archived, $from, $subject, $content, $length, NOW(), $priority, $emails_count) ";
		$this->DB->query($sql);
		
		$maillist_id = $this->DB->lastInsertId();
		
		foreach ($emails as $email) {
			$sql = "
				INSERT INTO {$this->DB->prefix()}maillist_emails
					(maillist_id, email)
				VALUES
					($maillist_id, {$this->DB->quote($email)}) ";
			$this->DB->query($sql);
		}
	}
	
	/**
	 * Получить информацию по рассылке.
	 *
	 * @param
	 * @return
	 */
	public function getMaillistInfo($maillist_id)
	{
	
		return true;
	}
	
	/**
	 * Получить общую информацию о состоянии демона рассылок, например кол-во активных задач. 
	 *
	 * @param
	 * @return array
	 */
	public function getStatus()
	{
	
		return true;
	}
	
	/**
	 * Запуск задач по расписанию.
	 *
	 * @return
	 */
	public function cron()
	{
		$cnt = $this->count_limit;
		
		// Удаляются все законченные рассылки, которые не являются архивными.
		$this->DB->exec("DELETE FROM {$this->DB->prefix()}maillist WHERE is_archived = 0 AND status = 0 ");
		
		$sql = "SELECT maillist_id, content, email_from, subject, is_archived
			FROM {$this->DB->prefix()}maillist
			WHERE status = 1
			ORDER BY priority ASC, datetime ASC ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$sql2 = "SELECT email FROM {$this->DB->prefix()}maillist_emails WHERE maillist_id = '{$row->maillist_id}' ";
			$result2 = $this->DB->query($sql2);
			while ($row2 = $result2->fetchObject()) {
				if ($this->sendEmail($row2->email, $row->subject, $row->email_from, $row->content)) {
					// Успешно отправлено.
					if ($row->is_archived) {
						$sql = "
							INSERT INTO {$this->DB->prefix()}maillist_email_archive
								(maillist_id, email, datetime)
							VALUES
								('{$row->maillist_id}', $email, NOW() ) ";
						$this->DB->query($sql);
					}
					
					$sql = "DELETE FROM {$this->DB->prefix()}maillist_emails
						WHERE maillist_id = '{$row->maillist_id}'
						AND email = '{$row2->email}' ";
					$this->DB->exec($sql);
					
					if ($cnt-- == 0) {
						return true;
					}
				} else {
					// @todo Ошибка отправки письма.
				}				
			}
			
			// Если обработчик отсылки писем законил работу, то подразумевается, что список рассылки можно делать неактивным.
			$sql = "
				UPDATE {$this->DB->prefix()}maillist SET
					datetime_end = NOW(),
					status = 0
				WHERE maillist_id = '{$row->maillist_id}' ";
			$this->DB->query($sql);
		}
		
		return true;
	}
	
	/**
	 * Метод отправки емаила.
	 *
	 * @param
	 * @return
	 */
	protected function sendEmail($to, $from, $subject, $content)
	{
		return @mail($to, $subject, $content, "From: " . $from . "\nContent-type: text/plain; charset=utf-8") ? true : false;
	}
}