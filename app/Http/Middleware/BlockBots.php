<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class BlockBots
{
    /**
     * Known bot user agents to block
     * Based on current data from multiple authoritative sources:
     * - Google Search Central Documentation (2025)
     * - Human Security Bot Analytics (2024-2025)
     * - Foundation Web Dev AI Crawlers List (2024)
     * - GitHub crawler-user-agents repository
     */
    private array $blockedBots = [
        // Search Engine Bots
        'Googlebot',
        'bingbot',
        'YandexBot',
        'Slurp', // Yahoo
        'DuckDuckBot',
        'Baiduspider',

        // AI/LLM Crawlers (Major ones as of 2024-2025)
        'GPTBot', // OpenAI
        'OAI-SearchBot', // OpenAI SearchBot
        'ChatGPT-User',
        'ClaudeBot', // Anthropic
        'anthropic-ai', // Legacy Anthropic
        'PerplexityBot',
        'Bytespider', // ByteDance/TikTok
        'CCBot', // Common Crawl
        'ImagesiftBot',
        'cohere-ai',
        'Google-Extended', // Google AI training

        // Social Media Bots
        'facebookexternalhit',
        'Facebot',
        'Twitterbot',
        'LinkedInBot',
        'WhatsApp',
        'DiscordBot',
        'TelegramBot',
        'Slackbot',
        'PinterestBot',
        'AppleBot', // Siri/Spotlight

        // Development/Testing Tools
        'curl',
        'wget',
        'python-requests',
        'PostmanRuntime',
        'HTTPie',
        'Go-http-client',
        'okhttp',
        'Apache-HttpClient',
        'python-urllib',
        'node-fetch',
        'axios',
        'libwww-perl',

        // Other Common Crawlers
        'ia_archiver', // Internet Archive
        'SemrushBot',
        'AhrefsBot',
        'MJ12bot',
        'DotBot',
        'Yeti', // Naver (Korean search engine)
    ];

    /**
     * Suspicious patterns in user agents
     */
    private array $suspiciousPatterns = [
        '/bot/i',
        '/crawl/i',
        '/spider/i',
        '/scrape/i',
        '/harvest/i',
        '/extract/i',
        '/libwww/i',
        '/lwp/i',
        '/mechanize/i',
        '/selenium/i',
        '/phantomjs/i',
        '/headless/i',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (ResponseAlias)  $next
     */
    public function handle(Request $request, Closure $next): ResponseAlias
    {
        $userAgent = $request->userAgent();

        // Block if no user agent
        if (empty($userAgent)) {
            return $this->blockResponse('No user agent provided');
        }

        // Check against known bot user agents
        foreach ($this->blockedBots as $bot) {
            if (stripos($userAgent, $bot) !== false) {
                return $this->blockResponse("Blocked bot: {$bot}");
            }
        }

        // Check against suspicious patterns
        foreach ($this->suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return $this->blockResponse("Suspicious user agent pattern detected");
            }
        }

        // Additional checks
        if ($this->isLikelyBot($request)) {
            return $this->blockResponse('Bot-like behavior detected');
        }

        return $next($request);
    }

    /**
     * Additional bot detection heuristics
     */
    private function isLikelyBot(Request $request): bool
    {
        $userAgent = $request->userAgent();

        // Very short user agents (likely custom bots)
        if (strlen($userAgent) < 10) {
            return true;
        }

        // Missing common browser headers
        if (!$request->hasHeader('Accept-Language') &&
            !$request->hasHeader('Accept-Encoding')) {
            return true;
        }

        // Suspicious referer patterns
        $referer = $request->header('referer');
        if ($referer && preg_match('/\.(tk|ml|ga|cf)($|\/)/i', $referer)) {
            return true;
        }

        return false;
    }

    /**
     * Return blocked response
     */
    private function blockResponse(string $reason = 'Access denied'): ResponseAlias
    {
        // Log the attempt if needed
        logger()->warning('Bot access blocked', [
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'reason' => $reason,
        ]);

        return response('Access Denied', Response::HTTP_FORBIDDEN);
    }
}