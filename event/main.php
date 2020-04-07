<?php
/**
 *
 * phpBB Studio - Embed Video Attachment. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\eva\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * phpBB Studio - Embed Video Attachment main listener.
 */
class main implements EventSubscriberInterface
{
	/** @array	array	File extension and mimetype allowed */
	protected $extension = [
		'mp4'	=> 'video/mp4',
		'webm'	=> 'video/webm',
		'ogg'	=> 'audio/ogg',
		'ogv'	=> 'video/ogg',
	];

	/**
	 * Assign functions defined in this class to event listeners in the core.
	 *
	 * @return array
	 * @static
	 */
	static public function getSubscribedEvents()
	{
		return [
			'core.send_file_to_browser_before'				=> 'eva_send_file_to_browser_before',
			'core.parse_attachments_modify_template_data'	=> 'eva_parse_attachments_modify_template_data',
		];
	}

	/**
	 * Preserve mimetype for the supported video formats.
	 *
	 * @event  core.send_file_to_browser_before
	 * @param \phpbb\event\data		$event	The event object
	 *
	 * @return void
	 */
	public function eva_send_file_to_browser_before($event)
	{
		if (array_key_exists($event['attachment']['extension'], $this->extension))
		{
			$attachment = $event['attachment'];

			$attachment['mimetype'] = $this->extension[$event['attachment']['extension']];

			$event['attachment'] = $attachment;
		}
	}

	/**
	 * Checks and allows embedding the file if supported.
	 *
	 * @event  core.parse_attachments_modify_template_data
	 * @param \phpbb\event\data		$event	The event object
	 *
	 * @return void
	 */
	public function eva_parse_attachments_modify_template_data($event)
	{
		/** If attachments are allowed in this forum */
		if (!isset($event['block_array']['S_DENIED']))
		{
			/** If the file extension is included in our array */
			if (array_key_exists($event['attachment']['extension'], $this->extension))
			{
				/** Let's embed the video(s) */
				$event['block_array'] = array_merge($event['block_array'], [
					'URL'		=> append_sid($event['download_link'] . '&amp;mode=view'),
					'MIMETYPE'	=> $this->extension[$event['attachment']['extension']],
					'S_VIDEO'	=> true,
				]);

				$block_array = $event['block_array'];

				/** We do not need a linked filename */
				unset($block_array['S_FILE']);

				/** The file is not an image */
				unset($block_array['S_IMAGE']);

				$event['block_array'] = $block_array;
			}
		}
	}
}
