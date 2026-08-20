---
title: WebberZone Settings API
description: A reusable PHP library for building tabbed WordPress settings pages, metaboxes, and setup wizards — the admin framework behind every WebberZone plugin.
permalink: /
---

<div class="hero">
  <button type="button" class="agent-pill" data-agent-prompt="Fetch and execute the appropriate instructions to set up the WebberZone Settings API in my plugin from https://webberzone.github.io/Settings_API/prompt.md">
    <span class="agent-pill-label">Set up the Settings API with your coding agent</span>
    <svg class="agent-pill-copy" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
    <svg class="agent-pill-check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
  </button>
  <div class="eyebrow">Free &middot; Open Source &middot; Copy &amp; Own</div>
  <h1>WordPress settings pages, <em>without the boilerplate</em></h1>
  <p class="lead">The WebberZone Settings API wraps the native WordPress Settings API in a configuration-driven layer: declare your fields once as an array, and get a tabbed settings page, sanitization, defaults, metaboxes, and a setup wizard for free.</p>
  <div class="hero-ctas">
    <a href="{{ '/docs/' | relative_url }}" class="btn-primary">Read the Docs</a>
    <a href="https://github.com/WebberZone/Settings_API" target="_blank" class="btn-outline">View on GitHub</a>
    <a href="{{ '/docs/01-wzsa-getting-started/integrating-the-settings-api/' | relative_url }}" class="btn-outline">Integration Guide</a>
  </div>
  <p class="agent-prompt">Fetch and execute the appropriate instructions to set up the WebberZone Settings API in my plugin from <a href="{{ '/prompt.md' | relative_url }}">https://webberzone.github.io/Settings_API/prompt.md</a></p>
</div>

<div class="home-section">
  <div class="eyebrow">How it works</div>
  <h2 class="section-title" style="margin-bottom:8px;">One array in, a complete admin screen out</h2>
  <p style="color:var(--wz-warm-grey); max-width:64ch;">There is no Composer package and no build step. You copy the library into your plugin, rename the namespace, and describe your settings as PHP arrays.</p>

  <div class="steps-grid">
    <div class="step">
      <h3>Declare your fields</h3>
      <p>A nested array keyed by tab, then by field ID. Each field names its <code>type</code>, label, description, and default &mdash; 26 field types are supported, from <code>text</code> and <code>toggle</code> through <code>repeater</code> and encrypted <code>sensitive</code> keys.</p>
    </div>
    <div class="step">
      <h3>Instantiate the API</h3>
      <p><code>Settings_API</code> takes an option key, a hook prefix, and your arrays. It registers the menu page, calls <code>register_setting()</code> and <code>add_settings_field()</code> for you, enqueues the colour picker, CodeMirror, Tom Select, and media uploader, and seeds defaults on first load.</p>
    </div>
    <div class="step">
      <h3>Read values anywhere</h3>
      <p><code>Options_API</code> is the read/write layer your plugin exposes to its own code &mdash; blog-aware caching, per-key filters, defaults resolution, and multisite-safe reads through <code>get_blog_option()</code>.</p>
    </div>
  </div>
</div>

<div class="home-section" style="padding-top:0;">
  <div class="eyebrow">What you get</div>
  <h2 class="section-title" style="margin-bottom:16px;">Batteries included</h2>

  <div class="feature-grid">
    <div class="feature-card">
      <h3>26 field types</h3>
      <p>Text, URL, CSV, colour, number, select, checkbox, toggle, multicheck, radio, post types, taxonomies, WYSIWYG, CSS/HTML editors, file pickers, repeaters, and more.</p>
    </div>
    <div class="feature-card">
      <h3>Sanitization by type</h3>
      <p>Every field type maps to a matching <code>sanitize_*_field()</code> callback at save time, with per-field overrides via <code>sanitize_callback</code>.</p>
    </div>
    <div class="feature-card">
      <h3>Encrypted API keys</h3>
      <p>The <code>sensitive</code> field type encrypts values at rest using OpenSSL, falling back to libsodium, and masks them in the UI.</p>
    </div>
    <div class="feature-card">
      <h3>Setup wizard</h3>
      <p>An optional multi-step guided wizard that reuses the same field definitions and writes straight into your plugin's options.</p>
    </div>
    <div class="feature-card">
      <h3>Metaboxes for free</h3>
      <p>Feed the same field array to <code>Metabox_API</code> and get a post metabox saving to <code>_{prefix}_{field_id}</code> meta keys.</p>
    </div>
    <div class="feature-card">
      <h3>Filters everywhere</h3>
      <p>Dynamic, prefix-scoped hooks let a consuming plugin adjust defaults, sanitization, rendered HTML, help tabs, and the sidebar.</p>
    </div>
  </div>
</div>

<div class="home-section" style="padding-top:0;">
  <div class="eyebrow">Get started</div>
  <h2 class="section-title" style="margin-bottom:16px;">Documentation</h2>
  <div class="card-grid">
    <a class="doc-card" href="{{ '/docs/01-wzsa-getting-started/what-is-the-webberzone-settings-api/' | relative_url }}">
      <h3>Overview</h3>
      <p>What the library is, what is in the repository, and how the pieces fit together.</p>
    </a>
    <a class="doc-card" href="{{ '/docs/01-wzsa-getting-started/integrating-the-settings-api/' | relative_url }}">
      <h3>Integration</h3>
      <p>Copy the files, rename the namespace, and wire the settings page into WordPress.</p>
    </a>
    <a class="doc-card" href="{{ '/docs/02-wzsa-core-classes/field-types-reference/' | relative_url }}">
      <h3>Field Types</h3>
      <p>Every supported field type, its arguments, and how it is sanitized.</p>
    </a>
    <a class="doc-card" href="{{ '/docs/03-wzsa-extending/hooks-and-filters-reference/' | relative_url }}">
      <h3>Hooks &amp; Filters</h3>
      <p>The full list of prefix-scoped filters and actions the library fires.</p>
    </a>
  </div>
</div>

<script>
(function () {
  var pill = document.querySelector('.agent-pill');
  if (!pill) { return; }

  var label = pill.querySelector('.agent-pill-label');
  var original = label.textContent;
  var prompt = pill.getAttribute('data-agent-prompt');
  var timer;

  function done() {
    label.textContent = 'Copied \u2014 paste it into your agent';
    pill.classList.add('is-copied');
    clearTimeout(timer);
    timer = setTimeout(function () {
      label.textContent = original;
      pill.classList.remove('is-copied');
    }, 2600);
  }

  function fallback() {
    var field = document.createElement('textarea');
    field.value = prompt;
    field.setAttribute('readonly', '');
    field.style.position = 'fixed';
    field.style.opacity = '0';
    document.body.appendChild(field);
    field.select();
    try { document.execCommand('copy'); done(); } catch (e) { window.prompt('Copy this prompt:', prompt); }
    document.body.removeChild(field);
  }

  pill.addEventListener('click', function () {
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(prompt).then(done, fallback);
    } else {
      fallback();
    }
  });
}());
</script>
