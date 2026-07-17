import { defineConfig } from 'vitepress'
import phpDocPlugin from './phpdoc.js'
import { ctnGrammar } from './ctn.tmLanguage.js'

// https://vitepress.dev/reference/site-config
export default defineConfig({
  title: 'CC Container',
  description: 'A PHP Service Container featuring a simple meta-language with fast and compilable dependency injection.',

  // GitHub Pages project site is served from /Container/
  base: '/Container/',

  // The original clancats.io theme is light-only; lock the site to light
  // and remove the dark-mode toggle.
  appearance: false,

  // Fail the build on broken internal links.
  ignoreDeadLinks: false,

  markdown: {
    // Dark syntax highlighting theme, even though the site chrome is light.
    theme: 'github-dark',

    // `.ctn` container files use a dedicated TextMate grammar that mirrors the
    // official VSCode extension (see ./ctn.tmLanguage.js).
    languages: [ctnGrammar],

    // `[~ PHPDoc](/src/File.php#method)` directives become tabbed method blocks
    // generated from the referenced PHP source (see ./phpdoc.js).
    config: (md) => {
      md.use(phpDocPlugin)
    },
  },

  themeConfig: {
    logo: 'http://clancats.io/assets/media/img/logo/container.png',

    nav: [
      { text: 'Getting Started', link: '/introduction/getting-started' },
      { text: 'CTN Files', link: '/container-files/introduction' },
      { text: 'Packagist', link: 'https://packagist.org/packages/clancats/container' },
    ],

    sidebar: [
      {
        text: 'Introduction',
        items: [
          { text: 'Overview', link: '/introduction/overview' },
          { text: 'Installation', link: '/introduction/installation' },
          { text: 'Getting Started', link: '/introduction/getting-started' },
          { text: 'Core Concepts', link: '/introduction/core-concepts' },
        ],
      },
      {
        text: 'Usage',
        items: [
          { text: 'The Container Lifecycle', link: '/usage/lifecycle' },
          { text: 'Using the Container', link: '/usage/container' },
          { text: 'Parameters', link: '/usage/parameters' },
          { text: 'Services', link: '/usage/services' },
        ],
      },
      {
        text: 'Container Files (.ctn)',
        items: [
          { text: 'Introduction', link: '/container-files/introduction' },
          { text: 'Values & Types', link: '/container-files/values' },
          { text: 'Parameters', link: '/container-files/parameters' },
          { text: 'Services', link: '/container-files/services' },
          { text: 'Metadata & Tags', link: '/container-files/metadata' },
          { text: 'Generics', link: '/container-files/generics' },
          { text: 'Imports & Overriding', link: '/container-files/imports' },
          {
            text: 'Loading & Integration',
            items: [
              { text: 'Parsing', link: '/container-files/parsing' },
              { text: 'Custom Namespace', link: '/container-files/custom-namespace' },
              { text: 'Composer Integration', link: '/container-files/composer-integration' },
            ],
          },
        ],
      },
      {
        text: 'Implementations',
        items: [
          { text: 'Choosing an Implementation', link: '/usage/implementations/choosing' },
          { text: 'Default', link: '/usage/implementations/default' },
          { text: 'Dynamic', link: '/usage/implementations/dynamic' },
          { text: 'Custom Build', link: '/usage/implementations/custom-build' },
        ],
      },
      {
        text: 'Programmatic API',
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
