<?php

namespace App\Services;

use App\Models\WoRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WahaService
{
    /**
     * Get the base URL from configuration.
     *
     * @return string
     */
    protected function getBaseUrl(): string
    {
        return rtrim(config('services.waha.base_url', 'http://waha:3001'), '/');
    }

    /**
     * Get the API key from configuration.
     *
     * @return string|null
     */
    protected function getApiKey(): ?string
    {
        return config('services.waha.api_key');
    }

    /**
     * Get the default session name from configuration.
     *
     * @return string
     */
    protected function getSession(): string
    {
        return config('services.waha.session', 'default');
    }

    /**
     * Centralized HTTP client request method for WAHA.
     *
     * @param string $method
     * @param string $path
     * @param array $payload
     * @param array $query
     * @return array|null
     */
    public function request(string $method, string $path, array $payload = [], array $query = []): ?array
    {
        try {
            $url = $this->getBaseUrl() . '/' . ltrim($path, '/');
            $apiKey = $this->getApiKey();

            $headers = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ];

            if (!empty($apiKey)) {
                $headers['X-Api-Key'] = $apiKey;
            }

            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->send($method, $url, [
                    'query' => $query,
                    'json' => $payload,
                ]);

            if ($response->failed()) {
                Log::error("WAHA: Request [{$method}] {$path} failed. Status: {$response->status()}, Response: {$response->body()}");
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error("WAHA: Exception in request [{$method}] {$path}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Send a WhatsApp notification when a new Work Order is created.
     *
     * @param array $wahaPayload
     * @return void
     */
    public function sendNewWoNotification(array $wahaPayload): void
    {
        try {
            $phone = $wahaPayload['phone'] ?? null;
            $taskMaster = $wahaPayload['taskMaster'] ?? null;
            $taskDetail = $wahaPayload['taskDetail'] ?? null;

            if (empty($phone)) {
                Log::warning("No phone number provided for new WO notification");
                return;
            }

            if (!$taskMaster || !$taskDetail) {
                Log::warning("Invalid payload for new WO notification");
                return;
            }

            $message = $this->buildMessage($taskMaster, $taskDetail);
            $this->sendText($phone, $message);
        } catch (\Exception $e) {
            Log::error("Exception occurred while sending notification for Task Code: {$taskMaster['code']}. Error: " . $e->getMessage());
        }
    }

    /**
     * Send a plain text message to a phone number.
     *
     * @param string $phone
     * @param string $text
     * @param array $options
     * @return bool
     */
    public function sendText(string $phone, string $text, array $options = []): bool
    {
        $formattedPhone = $this->formatPhoneNumber($phone);
        if (!$formattedPhone) {
            Log::warning("WAHA: Invalid phone number: {$phone}");
            return false;
        }

        $payload = array_merge([
            'session' => $this->getSession(),
            'chatId' => $formattedPhone,
            'text' => $text,
        ], $options);

        $result = $this->request('POST', 'api/sendText', $payload);
        return !is_null($result);
    }

    /**
     * Send a WhatsApp message (Backward compatibility wrapper).
     *
     * @param string $phone
     * @param string $message
     * @return bool
     */
    public function sendMessage(string $phone, string $message): bool
    {
        return $this->sendText($phone, $message);
    }

    /**
     * Send an image file to a phone number.
     *
     * @param string $phone
     * @param string $url
     * @param string $caption
     * @param array $options
     * @return bool
     */
    public function sendImage(string $phone, string $url, string $caption = '', array $options = []): bool
    {
        $formattedPhone = $this->formatPhoneNumber($phone);
        if (!$formattedPhone) return false;

        $payload = array_merge([
            'session' => $this->getSession(),
            'chatId' => $formattedPhone,
            'file' => [
                'url' => $url,
            ],
            'caption' => $caption,
        ], $options);

        $result = $this->request('POST', 'api/sendImage', $payload);
        return !is_null($result);
    }

    /**
     * Send a generic document/file to a phone number.
     *
     * @param string $phone
     * @param string $url
     * @param string $filename
     * @param string $caption
     * @param array $options
     * @return bool
     */
    public function sendFile(string $phone, string $url, string $filename = '', string $caption = '', array $options = []): bool
    {
        $formattedPhone = $this->formatPhoneNumber($phone);
        if (!$formattedPhone) return false;

        $payload = array_merge([
            'session' => $this->getSession(),
            'chatId' => $formattedPhone,
            'file' => [
                'url' => $url,
            ],
            'filename' => $filename ?: basename($url),
            'caption' => $caption,
        ], $options);

        $result = $this->request('POST', 'api/sendFile', $payload);
        return !is_null($result);
    }

    /**
     * Send voice note to a phone number.
     *
     * @param string $phone
     * @param string $url
     * @param array $options
     * @return bool
     */
    public function sendVoice(string $phone, string $url, array $options = []): bool
    {
        $formattedPhone = $this->formatPhoneNumber($phone);
        if (!$formattedPhone) return false;

        $payload = array_merge([
            'session' => $this->getSession(),
            'chatId' => $formattedPhone,
            'file' => [
                'url' => $url,
            ],
        ], $options);

        $result = $this->request('POST', 'api/sendVoice', $payload);
        return !is_null($result);
    }

    /**
     * Send video note to a phone number.
     *
     * @param string $phone
     * @param string $url
     * @param string $caption
     * @param array $options
     * @return bool
     */
    public function sendVideo(string $phone, string $url, string $caption = '', array $options = []): bool
    {
        $formattedPhone = $this->formatPhoneNumber($phone);
        if (!$formattedPhone) return false;

        $payload = array_merge([
            'session' => $this->getSession(),
            'chatId' => $formattedPhone,
            'file' => [
                'url' => $url,
            ],
            'caption' => $caption,
        ], $options);

        $result = $this->request('POST', 'api/sendVideo', $payload);
        return !is_null($result);
    }

    /**
     * Send location coordinates to a phone number.
     *
     * @param string $phone
     * @param float $latitude
     * @param float $longitude
     * @param string $title
     * @param string $address
     * @return bool
     */
    public function sendLocation(string $phone, float $latitude, float $longitude, string $title = '', string $address = ''): bool
    {
        $formattedPhone = $this->formatPhoneNumber($phone);
        if (!$formattedPhone) return false;

        $payload = [
            'session' => $this->getSession(),
            'chatId' => $formattedPhone,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'title' => $title,
            'description' => $address,
        ];

        $result = $this->request('POST', 'api/sendLocation', $payload);
        return !is_null($result);
    }

    /**
     * Send a Link with custom preview.
     *
     * @param string $phone
     * @param string $url
     * @param string $title
     * @return bool
     */
    public function sendLinkPreview(string $phone, string $url, string $title = ''): bool
    {
        $formattedPhone = $this->formatPhoneNumber($phone);
        if (!$formattedPhone) return false;

        $payload = [
            'session' => $this->getSession(),
            'chatId' => $formattedPhone,
            'url' => $url,
            'title' => $title,
        ];

        $result = $this->request('POST', 'api/sendLinkPreview', $payload);
        return !is_null($result);
    }

    /**
     * Mark a chat as read / seen.
     *
     * @param string $phone
     * @return bool
     */
    public function sendSeen(string $phone): bool
    {
        $formattedPhone = $this->formatPhoneNumber($phone);
        if (!$formattedPhone) return false;

        $payload = [
            'session' => $this->getSession(),
            'chatId' => $formattedPhone,
        ];

        $result = $this->request('POST', 'api/sendSeen', $payload);
        return !is_null($result);
    }

    /**
     * Send a WhatsApp poll to a phone number.
     *
     * @param string $phone
     * @param string $question
     * @param array $pollOptions Array of option strings
     * @param bool $multipleAnswers Allow voting for multiple items
     * @return bool
     */
    public function sendPoll(string $phone, string $question, array $pollOptions, bool $multipleAnswers = false): bool
    {
        $formattedPhone = $this->formatPhoneNumber($phone);
        if (!$formattedPhone) return false;

        $payload = [
            'session' => $this->getSession(),
            'chatId' => $formattedPhone,
            'poll' => [
                'name' => $question,
                'options' => $pollOptions,
                'multipleAnswers' => $multipleAnswers,
            ]
        ];

        $result = $this->request('POST', 'api/sendPoll', $payload);
        return !is_null($result);
    }

    /**
     * Start "typing..." status indicator in a chat.
     *
     * @param string $phone
     * @return bool
     */
    public function startTyping(string $phone): bool
    {
        $formattedPhone = $this->formatPhoneNumber($phone);
        if (!$formattedPhone) return false;

        $payload = [
            'session' => $this->getSession(),
            'chatId' => $formattedPhone,
        ];

        $result = $this->request('POST', 'api/startTyping', $payload);
        return !is_null($result);
    }

    /**
     * Stop "typing..." status indicator in a chat.
     *
     * @param string $phone
     * @return bool
     */
    public function stopTyping(string $phone): bool
    {
        $formattedPhone = $this->formatPhoneNumber($phone);
        if (!$formattedPhone) return false;

        $payload = [
            'session' => $this->getSession(),
            'chatId' => $formattedPhone,
        ];

        $result = $this->request('POST', 'api/stopTyping', $payload);
        return !is_null($result);
    }

    /**
     * Check if a phone number exists on WhatsApp.
     *
     * @param string $phone
     * @return array|null
     */
    public function checkNumberStatus(string $phone): ?array
    {
        $formattedPhone = $this->formatPhoneNumber($phone);
        if (!$formattedPhone) return null;

        $query = [
            'session' => $this->getSession(),
            'phone' => str_replace('@c.us', '', $formattedPhone),
        ];

        return $this->request('GET', 'api/checkNumberStatus', [], $query);
    }

    /**
     * Start a WhatsApp session.
     *
     * @param string|null $name
     * @return array|null
     */
    public function startSession(string $name = null): ?array
    {
        $session = $name ?: $this->getSession();
        return $this->request('POST', 'api/sessions/start', ['name' => $session]);
    }

    /**
     * Stop a WhatsApp session.
     *
     * @param string|null $name
     * @return array|null
     */
    public function stopSession(string $name = null): ?array
    {
        $session = $name ?: $this->getSession();
        return $this->request('POST', 'api/sessions/stop', ['name' => $session]);
    }

    /**
     * Restart a WhatsApp session.
     *
     * @param string|null $name
     * @return array|null
     */
    public function restartSession(string $name = null): ?array
    {
        $session = $name ?: $this->getSession();
        return $this->request('POST', "api/sessions/{$session}/restart");
    }

    /**
     * Get a WhatsApp session profile detail.
     *
     * @param string|null $name
     * @return array|null
     */
    public function getSessionInfo(string $name = null): ?array
    {
        $session = $name ?: $this->getSession();
        return $this->request('GET', "api/sessions/{$session}");
    }

    /**
     * Get list of all WhatsApp sessions.
     *
     * @return array|null
     */
    public function getSessions(): ?array
    {
        return $this->request('GET', 'api/sessions');
    }

    /**
     * Get QR Code for pairing session (as raw text, JSON, or image).
     *
     * @param string|null $name
     * @param string $format 'image', 'json', or 'raw'
     * @return string|array|null
     */
    public function getQrCode(string $name = null, string $format = 'image')
    {
        $session = $name ?: $this->getSession();
        $query = ['format' => $format];

        try {
            $url = $this->getBaseUrl() . "/api/{$session}/auth/qr";
            $apiKey = $this->getApiKey();
            $headers = [];
            if (!empty($apiKey)) {
                $headers['X-Api-Key'] = $apiKey;
            }

            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->get($url, $query);

            if ($response->failed()) {
                Log::error("WAHA: Failed to get QR Code for session {$session}");
                return null;
            }

            if ($format === 'image') {
                return $response->body(); // Binary image
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error("WAHA: Exception in getQrCode: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get current WAHA server status.
     *
     * @return array|null
     */
    public function getServerStatus(): ?array
    {
        return $this->request('GET', 'api/server/status');
    }

    /**
     * Format raw phone number or group/channel ID into WAHA format (e.g. 628123456789@c.us, 120363043@g.us, or 120363043@newsletter).
     *
     * @param string $phone
     * @return string|null
     */
    public function formatPhoneNumber(string $phone): ?string
    {
        $phone = trim($phone);
        if (empty($phone)) {
            return null;
        }

        // If it already has a valid WAHA suffix (@g.us, @c.us, @newsletter), preserve the suffix
        if (str_contains($phone, '@')) {
            list($id, $domain) = explode('@', $phone, 2);
            // WhatsApp Group IDs can contain hyphens (e.g. 628123-160123@g.us), so we allow numbers, +, -, _
            $idCleaned = preg_replace('/[^0-9+\-_]/', '', $id);
            return $idCleaned . '@' . $domain;
        }

        // Otherwise, treat as regular phone number
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);

        // Remove leading plus
        if (str_starts_with($cleaned, '+')) {
            $cleaned = substr($cleaned, 1);
        }

        // Convert leading 0 to 62
        if (str_starts_with($cleaned, '0')) {
            $cleaned = '62' . substr($cleaned, 1);
        }

        if (empty($cleaned)) {
            return null;
        }

        return $cleaned . '@c.us';
    }

    /**
     * Send a WhatsApp message to a Group.
     *
     * @param string $groupId (e.g., "120363043812738@g.us" or "120363043812738")
     * @param string $message
     * @return bool
     */
    public function sendToGroup(string $groupId, string $message): bool
    {
        if (!str_contains($groupId, '@')) {
            $groupId .= '@g.us';
        }
        return $this->sendText($groupId, $message);
    }

    /**
     * Send a WhatsApp message to a Channel (Newsletter).
     *
     * @param string $channelId (e.g., "120363043812738@newsletter" or "120363043812738")
     * @param string $message
     * @return bool
     */
    public function sendToChannel(string $channelId, string $message): bool
    {
        if (!str_contains($channelId, '@')) {
            $channelId .= '@newsletter';
        }
        return $this->sendText($channelId, $message);
    }

    /**
     * Get messages from a chat (personal, group, or channel/newsletter).
     *
     * @param string $chatId (e.g. "628123456789@c.us" or "120363043812738@g.us")
     * @param array $query Optional query parameters (limit, downloadMedia, offset, etc.)
     * @return array|null
     */
    public function getMessages(string $chatId, array $query = []): ?array
    {
        $formattedChatId = $this->formatPhoneNumber($chatId);
        if (!$formattedChatId) {
            return null;
        }

        $session = $this->getSession();
        $path = "api/{$session}/chats/{$formattedChatId}/messages";

        return $this->request('GET', $path, [], $query);
    }

    /**
     * Get details of a WhatsApp Group.
     *
     * @param string $groupId
     * @return array|null
     */
    public function getGroupInfo(string $groupId): ?array
    {
        if (!str_contains($groupId, '@')) {
            $groupId .= '@g.us';
        }
        $session = $this->getSession();
        return $this->request('GET', "api/{$session}/groups/{$groupId}");
    }

    /**
     * Get list of all WhatsApp Groups the session is currently in.
     *
     * @return array|null
     */
    public function getGroups(): ?array
    {
        $session = $this->getSession();
        return $this->request('GET', "api/{$session}/groups");
    }

    /**
     * Build the message body.
     *
     * @param WoRequest $wo
     * @param mixed $requester
     * @return string
     */
    private function buildMessage($taskMaster, $taskDetail): string
    {
        $taskMasterCategory = ucfirst($taskMaster['category']['name'] ?? '-');
        $taskDetailCode = $taskDetail['code'] ?? '-';
        $taskDetailName = $taskDetail['activity'] ?? '-';
        // $taskMasterDesc = $taskMaster['description'] ?? '-';
        $taskDetailTime = date('d M Y H:i', strtotime($taskDetail['date_realization_start'])) .' - '. date('d M Y H:i', strtotime($taskDetail['date_realization_finish']));

        return "*Kode:* {$taskDetailCode}\n" .
               "*Kategori:* {$taskMasterCategory}\n" .
               "*Waktu:* {$taskDetailTime}\n\n" .
               "Tugas {$taskDetailName} Selesai Dikerjakan, Silahkan cek di aplikasi untuk melihat detailnya.";
    }
}
