import { defineConfig } from 'vitepress'

// https://vitepress.dev/reference/site-config
export default defineConfig({
  title: 'ClanCats Container',
  description: 'A PHP Service Container featuring a simple meta-language with fast and compilable dependency injection.',

  // GitHub Pages project site is served from /Container/
  base: '/Container/',

  // The original clancats.io theme is light-only; lock the site to light
  // and remove the dark-mode toggle.
  appearance: false,

  // Fail the build on broken internal links.
  ignoreDeadLinks: false,

  markdown: {
    // `.ctn` container files have no dedicated highlighter grammar; YAML is the
    // closest match for the `key: value` / `#` comment style.
    languageAlias: {
      ctn: 'yaml',
    },
  },

  themeConfig: {
    logo: 'http://clancats.io/assets/media/img/logo/container.png',

    nav: [
      { text: 'Getting Started', link: '/introduction/getting-started' },
      { text: 'Container Files', link: '/container-files/syntax' },
      { text: 'Packagist', link: 'https://packagist.org/packages/clancats/container' },
    ],

    sidebar: [
      {
        text: 'Introduction',
        items: [
          { text: 'Getting Started', link: '/introduction/getting-started' },
          { text: 'Core Concepts', link: '/introduction/core-concepts' },
        ],
      },
      {
        text: 'Usage',
        items: [
          { text: 'Container', link: '/usage/container' },
          { text: 'Parameters', link: '/usage/parameters' },
          { text: 'Services', link: '/usage/services' },
          {
            text: 'Implementations',
            items: [
              { text: 'Default', link: '/usage/implementations/default' },
              { text: 'Dynamic', link: '/usage/implementations/dynamic' },
              { text: 'Custom Build', link: '/usage/implementations/custom-build' },
            ],
          },
        ],
      },
      {
        text: 'Advanced',
        items: [
          { text: 'Service Binding', link: '/advanced/service-binding' },
          { text: 'Service Factories', link: '/advanced/service-factories' },
          { text: 'Service Definitions', link: '/advanced/service-definitions' },
          { text: 'Arguments', link: '/advanced/arguments' },
          {
            text: 'Service Providers',
            items: [
              { text: 'Using Service Providers', link: '/advanced/service-providers/using-service-providers' },
              { text: 'Array Service Provider', link: '/advanced/service-providers/array-service-provider' },
              { text: 'Custom Service Provider', link: '/advanced/service-providers/custom-service-provider' },
            ],
          },
        ],
      },
      {
        text: 'Container Files',
        items: [
          { text: 'Syntax', link: '/container-files/syntax' },
          { text: 'Parsing', link: '/container-files/parsing' },
          { text: 'Composer Integration', link: '/container-files/composer-integration' },
          { text: 'Custom Namespace', link: '/container-files/custom-namespace' },
        ],
      },
    ],

    socialLinks: [
      { icon: 'github', link: 'https://github.com/ClanCats/Container' },
    ],

    editLink: {
      pattern: 'https://github.com/ClanCats/Container/edit/master/docs/:path',
      text: 'Edit this page on GitHub',
    },

    search: {
      provider: 'local',
    },
  },
})
