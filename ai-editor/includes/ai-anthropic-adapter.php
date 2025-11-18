<?php
/**
 * Anthropic API Adapter for AI Client
 * Converts between OpenAI format (used by the app) and Anthropic format (used by Clove)
 */

class AnthropicAdapter {

    /**
     * Convert OpenAI-style messages to Anthropic format
     */
    public static function convertMessages($messages, $systemPrompt = '') {
        $anthropicMessages = [];
        $system = $systemPrompt;

        foreach ($messages as $msg) {
            if (!isset($msg['role']) || !isset($msg['content'])) {
                continue;
            }

            // Extract system messages
            if ($msg['role'] === 'system') {
                $system = $system ? $system . "\n" . $msg['content'] : $msg['content'];
                continue;
            }

            // Skip function messages for now
            if ($msg['role'] === 'function') {
                continue;
            }

            // Convert user/assistant messages
            $anthropicMessages[] = [
                'role' => $msg['role'],
                'content' => $msg['content']
            ];
        }

        return [
            'messages' => $anthropicMessages,
            'system' => $system
        ];
    }

    /**
     * Convert Anthropic response to OpenAI format
     */
    public static function convertResponse($anthropicResponse) {
        if (!is_array($anthropicResponse)) {
            $anthropicResponse = json_decode($anthropicResponse, true);
        }

        // Extract content
        $content = '';
        if (isset($anthropicResponse['content']) && is_array($anthropicResponse['content'])) {
            foreach ($anthropicResponse['content'] as $block) {
                if (isset($block['text'])) {
                    $content .= $block['text'];
                }
            }
        }

        // Calculate tokens
        $tokens = 0;
        if (isset($anthropicResponse['usage'])) {
            $tokens = ($anthropicResponse['usage']['input_tokens'] ?? 0) +
                     ($anthropicResponse['usage']['output_tokens'] ?? 0);
        }

        return [
            'success' => true,
            'content' => $content,
            'tokens_used' => $tokens,
            'role' => 'assistant',
            'finish_reason' => $anthropicResponse['stop_reason'] ?? 'stop',
            'tool_calls' => [] // Tool support would need additional implementation
        ];
    }
}
?>