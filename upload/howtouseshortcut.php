<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Shieldstack Upload Shortcut Guide</title>
    <link rel="manifest" href="manifest.webmanifest">
    <meta name="theme-color" content="#0b1f2a">
    <style>
        :root {
            color-scheme: dark;
            font-family: 'SF Pro Text', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #03080b;
            color: #f6fbff;
        }

        body {
            margin: 0;
            padding: 24px;
            line-height: 1.5;
        }

        header {
            margin-bottom: 24px;
        }

        h1, h2, h3 {
            font-weight: 600;
            margin: 0 0 12px;
        }

        p {
            margin: 0 0 12px;
            color: #cde4f7;
        }

        .card {
            background: rgba(11, 31, 42, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35);
        }

        ol, ul {
            margin: 0 0 12px 20px;
            padding: 0;
        }

        li + li {
            margin-top: 6px;
        }

        code {
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', monospace;
            background: rgba(255, 255, 255, 0.08);
            padding: 2px 6px;
            border-radius: 6px;
        }

        .actions {
            display: grid;
            gap: 8px;
        }

        .actions div {
            background: rgba(3, 8, 11, 0.8);
            border-radius: 12px;
            padding: 12px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        @media (min-width: 768px) {
            body {
                padding: 40px;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Build the iOS Shieldstack Upload Shortcut</h1>
        <p>Follow these steps to create an iOS Shortcut that uploads images to this server via <code>shortcutupload.php</code> and returns a direct link you can paste anywhere.</p>
    </header>

    <section class="card">
        <h2>What You Need</h2>
        <ul>
            <li>Any iPhone or iPad running iOS/iPadOS 15 or later with the Shortcuts app installed.</li>
            <li>Access to <code>https://shieldstack.dev/upload/shortcutupload.php</code> over HTTPS.</li>
            <li>Optional: Files saved under “Keep forever” should use ample storage, so toggle that request only when needed.</li>
        </ul>
    </section>

    <section class="card">
        <h2>Create the Shortcut</h2>
        <ol>
            <li>Open the Shortcuts app, tap <strong>+</strong>, and name it “Shieldstack Uploader”. Enable <strong>Show in Share Sheet</strong> so you can send photos straight from Photos or any app.</li>
            <li>In the shortcut details, set <strong>Share Sheet Types</strong> to <em>Images</em> so only photos/images appear as input.</li>
            <li>Add the action <strong>Select Photos</strong> (or skip this if you only run it from the share sheet). Turn on <strong>Select Multiple</strong> if you want batch uploads.</li>
            <li>Add <strong>Get Contents of URL</strong> and configure it:
                <ul>
                    <li><strong>URL</strong>: <code>https://shieldstack.dev/upload/shortcutupload.php</code></li>
                    <li><strong>Method</strong>: <em>POST</em></li>
                    <li><strong>Request Body</strong>: <em>Form</em> (multipart). Add a field named <code>file</code> and set its value to the Shortcut Input (tap the magic variable button and pick <em>Shortcut Input</em> or <em>Selected Photos</em>).</li>
                    <li>You can alternatively change <strong>Request Body</strong> to <em>File</em> and pass the Shortcut Input directly—the endpoint now accepts raw file bodies as well as multipart form data.</li>
                    <li>(Optional) Add another form field named <code>storage</code> with the text value <code>permanent</code> whenever you want the upload saved forever. Leave it out for temp uploads; if you post the file as raw binary instead of a form, append <code>?storage=permanent</code> to the URL instead.</li>
                </ul>
            </li>
            <li>Add <strong>Get Text from Input</strong> so the shortcut treats the server response as plain text. The API returns the fully qualified HTTPS URL on success or an <code>ERROR:</code> message on failure.</li>
            <li>Add <strong>Copy to Clipboard</strong> (ensuring “Notify When Done” is on) so the link is ready to paste. Follow it with <strong>Show Result</strong> or <strong>Quick Look</strong> to preview the copied URL.</li>
        </ol>
    </section>

    <section class="card">
        <h2>Behavior Notes</h2>
        <div class="actions">
            <div>
                <h3>Uploads & Limits</h3>
                <p>Files go to <code>uploads/temp/</code> unless you send <code>storage=permanent</code> either as a form field or via the query string. Each upload is capped at 10&nbsp;MB and only PNG, JPG/JPEG, GIF, and WebP are accepted—matching the main web app.</p>
            </div>
            <div>
                <h3>Direct Links</h3>
                <p>On success the endpoint replies with a single HTTPS URL such as <code>https://shieldstack.dev/upload/uploads/temp/&lt;random&gt;.jpg</code>. That URL is publicly reachable immediately.</p>
            </div>
            <div>
                <h3>Error Handling</h3>
                <p>If something goes wrong you will see <code>ERROR: ...</code> in place of the link. The text explains whether the file was too large, missing, or blocked for security.</p>
            </div>
        </div>
    </section>

    <section class="card">
        <h2>Quick Test</h2>
        <ol>
            <li>Pick a small PNG/JPG from Photos, tap <strong>Share</strong>, and choose your shortcut.</li>
            <li>Wait for the “Copied to Clipboard” toast and the pop-up result containing the HTTPS link.</li>
            <li>Open the link in Safari to confirm it renders inline. Use the manual <em>Clear temporary uploads</em> button on the main site if you want to prune the temp pool immediately.</li>
        </ol>
    </section>
</body>
</html>
