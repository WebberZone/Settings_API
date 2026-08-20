---
title: Documentation
permalink: /docs/
---

The [WebberZone Settings API](https://github.com/WebberZone/Settings_API) is a reusable PHP library that wraps the native WordPress Settings API. It powers the admin screens in Better Search, Contextual Related Posts, Knowledge Base, Top 10, and the rest of the WebberZone plugins. Browse the guides below.

<div class="card-grid">
  <a class="doc-card" href="{{ '/docs/01-wzsa-getting-started/what-is-the-webberzone-settings-api/' | relative_url }}">
    <h3>Overview</h3>
    <p>What the library does, what lives in the repository, and the namespaces it uses.</p>
  </a>
  <a class="doc-card" href="{{ '/docs/01-wzsa-getting-started/integrating-the-settings-api/' | relative_url }}">
    <h3>Integrating the library</h3>
    <p>Copy the files, rename the namespace, and register your settings page.</p>
  </a>
  <a class="doc-card" href="{{ '/docs/01-wzsa-getting-started/field-definition-format/' | relative_url }}">
    <h3>Field definition format</h3>
    <p>The array structure that drives fields, sections, and tabs.</p>
  </a>
  <a class="doc-card" href="{{ '/docs/02-wzsa-core-classes/settings-api-reference/' | relative_url }}">
    <h3>Settings_API reference</h3>
    <p>Constructor arguments, props, menu registration, and encryption helpers.</p>
  </a>
  <a class="doc-card" href="{{ '/docs/02-wzsa-core-classes/field-types-reference/' | relative_url }}">
    <h3>Field types reference</h3>
    <p>All 26 field types and the arguments each one understands.</p>
  </a>
  <a class="doc-card" href="{{ '/docs/02-wzsa-core-classes/options-api-reference/' | relative_url }}">
    <h3>Options_API reference</h3>
    <p>Reading and writing settings safely, including on multisite.</p>
  </a>
  <a class="doc-card" href="{{ '/docs/02-wzsa-core-classes/the-defaults-contract/' | relative_url }}">
    <h3>The defaults contract</h3>
    <p>Four rules that keep <code>get_defaults()</code> and <code>settings_defaults()</code> in sync.</p>
  </a>
  <a class="doc-card" href="{{ '/docs/03-wzsa-extending/setup-wizard-api/' | relative_url }}">
    <h3>Setup wizard</h3>
    <p>Multi-step guided onboarding built from the same field arrays.</p>
  </a>
  <a class="doc-card" href="{{ '/docs/03-wzsa-extending/hooks-and-filters-reference/' | relative_url }}">
    <h3>Hooks &amp; filters</h3>
    <p>Every prefix-scoped filter and action the library fires.</p>
  </a>
</div>
