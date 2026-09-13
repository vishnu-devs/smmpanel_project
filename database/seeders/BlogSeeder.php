<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Blog;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        if (Blog::count() > 0) {
            return;
        }

        $posts = [
            [
                'title' => 'Top 7 Proven Strategies to Grow Instagram Followers in 2026',
                'slug' => 'top-7-proven-strategies-to-grow-instagram-followers-2026',
                'category' => 'Instagram Growth',
                'excerpt' => 'Discover the most effective organic strategies and SMM boost methods to gain 10K+ genuine Instagram followers and viral reels reach this year.',
                'content' => '<h2>Why Instagram Growth in 2026 Requires a Smart Hybrid Strategy</h2>
<p>Instagram\'s algorithm has evolved significantly. In 2026, the key to viral growth is high engagement velocity—getting initial likes, views, and comments within the first 15 minutes of posting your Reels.</p>

<h3>1. Master High-Retention 9:16 Reels Hooks</h3>
<p>The first 3 seconds determine whether a user continues watching or swipes away. Use bold text hooks and captivating visual patterns to boost watch time percentage above 80%.</p>

<h3>2. Kickstart Initial Engagement Velocity with SMM Services</h3>
<p>When launching a new video or campaign, boosting your initial engagement with instant likes and reel views gives the Instagram algorithm the strong signal needed to push your content onto the Explore feed.</p>
<blockquote>Pro Tip: Always make sure "Flag for Review" is turned OFF in your Instagram settings before boosting followers.</blockquote>

<h3>3. Optimize Your Bio for Keyword Search (SEO)</h3>
<p>Instagram is now a search engine. Include your primary niche keywords in both your Display Name and Bio description so users searching for your industry find your profile first.</p>

<h3>4. Consistent Daily Posting Schedule</h3>
<p>Posting 1 to 2 high-quality reels per day during peak activity hours (typically 6:00 PM to 9:30 PM IST) yields the highest consistency for algorithm recognition.</p>',
                'author' => 'RishiSMM Team',
                'views' => 342,
                'status' => 'published',
            ],
            [
                'title' => 'How to Monetize Your YouTube Channel Fast with Watch Time & Subscribers',
                'slug' => 'how-to-monetize-youtube-channel-fast-watch-time-subscribers',
                'category' => 'YouTube Tips',
                'excerpt' => 'A complete step-by-step guide to reaching the 4,000 Watch Hours and 1,000 Subscribers milestone for the YouTube Partner Program.',
                'content' => '<h2>Unlocking the YouTube Partner Program (YPP) Fast</h2>
<p>To start earning AdSense revenue from YouTube, creators must hit the milestone of 1,000 Subscribers and 4,000 Valid Public Watch Hours within 12 months. Here is how top creators achieve this fast.</p>

<h3>1. Create High-Retention Long-Form Content</h3>
<p>Upload videos between 15 to 45 minutes long, such as in-depth tutorials, podcast discussions, or ambient background music. Longer videos rack up watch time hours significantly faster.</p>

<h3>2. Non-Drop Watch Time Boosting</h3>
<p>Utilizing high-retention YouTube Watch Time services from a trusted provider like RishiSMM can help bridge the gap for monetization while your organic audience builds up.</p>

<h3>3. Clickable CTR Thumbnails and Titles</h3>
<p>Ensure your thumbnail contrast is high and text is large enough to read clearly on mobile devices where over 75% of YouTube traffic originates.</p>',
                'author' => 'RishiSMM Team',
                'views' => 285,
                'status' => 'published',
            ],
            [
                'title' => 'How to Start Your Own Profitable SMM Panel Reseller Business in 2026',
                'slug' => 'how-to-start-profitable-smm-panel-reseller-business-2026',
                'category' => 'SMM Reseller',
                'excerpt' => 'Learn how to set up an automated SMM Child Panel or script, connect via REST API to root providers, and generate 40-70% monthly profit margins.',
                'content' => '<h2>Why the SMM Reselling Industry is Booming</h2>
<p>With millions of businesses, influencers, and digital marketing agencies investing heavily in digital presence, social media marketing panels are one of the most profitable online automated businesses today.</p>

<h3>1. Choose a Reliable SMM Script</h3>
<p>Platforms like PerfectPanel, SmartPanel, and RentASMM allow you to deploy a fully automated website with built-in user wallets, ticket systems, and API connectors in less than 24 hours.</p>

<h3>2. Connect to a Root API Provider with Direct Wholesale Rates</h3>
<p>Your profit margin depends directly on your source. Connecting your panel to RishiSMM gives you access to direct provider prices starting from ₹0.05 per 1K, allowing you to markup prices by 50% to 100%.</p>

<h3>3. Automate Payment Gateways with UPI & QR Codes</h3>
<p>In India, instant UPI QR payments with 12-digit UTR verification offer the highest conversion rates with zero transaction fee deductions.</p>',
                'author' => 'RishiSMM Team',
                'views' => 419,
                'status' => 'published',
            ],
            [
                'title' => 'Why Telegram Channel Engagement is Crucial for Crypto, Forex & E-commerce',
                'slug' => 'why-telegram-channel-engagement-is-crucial-crypto-forex-ecommerce',
                'category' => 'Telegram Growth',
                'excerpt' => 'Explore how genuine channel members, high post views, and auto emoji reactions build immense trust and convert cold visitors into buyers.',
                'content' => '<h2>Building Instant Social Proof on Telegram</h2>
<p>Telegram is the global hub for crypto traders, stock market analysts, e-commerce brands, and affiliate communities. When a visitor lands on your channel, they judge your credibility within 5 seconds based on member count and view ratio.</p>

<h3>1. Maintain a Natural Member-to-View Ratio</h3>
<p>A channel with 50,000 members but only 50 views per post looks suspicious. Always ensure your post views maintain a healthy 10% to 30% ratio relative to your total subscriber count.</p>

<h3>2. Auto Reactions for High Engagement Perception</h3>
<p>Adding positive emoji reactions (🔥, 👍, ❤️, 🚀) to new announcements dramatically boosts engagement and click-through rates on your pinned links.</p>',
                'author' => 'RishiSMM Team',
                'views' => 198,
                'status' => 'published',
            ]
        ];

        foreach ($posts as $data) {
            Blog::create($data);
        }
    }
}
