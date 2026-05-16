<?php
/**
 * AI Service - Handles communication with Google Gemini API
 */
class AIService {
    private $apiKey;
    private $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent";

    public function __construct($apiKey) {
        $this->apiKey = trim($apiKey);
    }

    /**
     * Extract CER triplets from text
     * 
     * @param string $text The reading material
     * @return array|false Array of triplets or false on failure
     */
    public function extractCER($text) {
        if (!$this->apiKey) return false;

        $prompt = "Extract Scientific Argumentation triplets (Claim, Evidence, Reasoning) from the following text. 
        Identify and extract ALL meaningful and logically complete triplets present in the text. 
        Do not invent information; if a claim lacks evidence or reasoning in the text, do not include it.
        
        IMPORTANT: Your response MUST be ONLY a raw JSON array of objects. Do not include any markdown formatting, backticks, or extra text.
        Each object MUST have exactly these keys: 'claim', 'evidence', 'reasoning'.
        
        Text:
        " . $text;

        $payload = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ]
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'x-goog-api-key: ' . $this->apiKey
        ]);
        
        // Bypass SSL issues for local development
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return false;
        }

        $result = json_decode($response, true);
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            $jsonText = $result['candidates'][0]['content']['parts'][0]['text'];
            
            // Clean up markdown if present
            $jsonText = preg_replace('/^```(?:json)?\s*|```\s*$/i', '', trim($jsonText));
            
            $triplets = json_decode($jsonText, true);
            return is_array($triplets) ? $triplets : false;
        }

        return false;
    }
}
