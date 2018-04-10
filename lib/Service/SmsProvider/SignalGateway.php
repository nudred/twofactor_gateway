<?php

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\TwoFactorSms\Service\SmsProvider;

use OCA\TwoFactorSms\Exception\SmsTransmissionException;
use OCA\TwoFactorSms\Service\ISmsService;
use OCP\Http\Client\IClientService;
use OCP\ILogger;

/**
 * An integration of https://gitlab.com/morph027/signal-web-gateway
 */
class SignalGateway implements ISmsService {

	/** @var IClientService */
	private $clientService;

	/** @var ILogger */
	private $logger;

	public function __construct(IClientService $clientService, ILogger $logger) {
		$this->clientService = $clientService;
		$this->logger = $logger;
	}

	/**
	 * @param string $recipient
	 * @param string $message
	 * @throws SmsTransmissionException
	 */
	public function send($recipient, $message) {
		// TODO: make configurable
		$endpoint = 'http://localhost:5000';

		$client = $this->clientService->newClient();
		$response = $client->post($endpoint, [
			'body' => [
				'to' => '+' . $recipient,
				'message' => $message,
			],
		]);
		$body = $response->getBody();
		$json = json_decode($body);

		if ($response->getStatusCode() !== 200 || is_null($json) || !is_array($json) || !isset($json['success']) || json['success'] !== true) {
			throw new SmsTransmissionException("error reported by Signal gateway, status=${$response->getStatusCode}, body=${$response->getBody()}");
		}
	}

}
