import {themes as prismThemes} from 'prism-react-renderer';
import type {Config} from '@docusaurus/types';
import type * as Preset from '@docusaurus/preset-classic';

// This runs in Node.js - Don't use client-side code here (browser APIs, JSX...)

const config: Config = {
  title: 'Symfony 6 ES CQRS Boilerplate',
  tagline: 'Domain-Driven Design, CQRS, and Event Sourcing with Symfony',
  favicon: 'img/favicon.ico',

  // Future flags, see https://docusaurus.io/docs/api/docusaurus-config#future
  future: {
    v4: true, // Improve compatibility with the upcoming Docusaurus v4
  },

  // Set the production url of your site here
  url: 'https://jorge07.github.io',
  // Set the /<baseUrl>/ pathname under which your site is served
  // For GitHub pages deployment, it is often '/<projectName>/'
  baseUrl: '/symfony-6-es-cqrs-boilerplate/',

  // GitHub pages deployment config.
  // If you aren't using GitHub pages, you don't need these.
  organizationName: 'jorge07', // Usually your GitHub org/user name.
  projectName: 'symfony-6-es-cqrs-boilerplate', // Usually your repo name.

  onBrokenLinks: 'throw',

  // Even if you don't use internationalization, you can use this field to set
  // useful metadata like html lang. For example, if your site is Chinese, you
  // may want to replace "en" with "zh-Hans".
  i18n: {
    defaultLocale: 'en',
    locales: ['en'],
  },

  presets: [
    [
      'classic',
      {
        docs: {
          sidebarPath: './sidebars.ts',
          // Please change this to your repo.
          // Remove this to remove the "edit this page" links.
          editUrl:
            'https://github.com/jorge07/symfony-6-es-cqrs-boilerplate/tree/master/',
        },
        blog: false, // Disable blog feature
        theme: {
          customCss: './src/css/custom.css',
        },
      } satisfies Preset.Options,
    ],
  ],

  themeConfig: {
    // Replace with your project's social card
    image: 'img/docusaurus-social-card.jpg',
    colorMode: {
      respectPrefersColorScheme: true,
    },
    navbar: {
      title: 'Symfony 6 ES CQRS Boilerplate',
      logo: {
        alt: 'Symfony Logo',
        src: 'img/logo.svg',
      },
      items: [
        {
          type: 'docSidebar',
          sidebarId: 'dddSidebar',
          position: 'left',
          label: 'DDD',
        },
        {
          type: 'docSidebar',
          sidebarId: 'cqrsSidebar',
          position: 'left',
          label: 'CQRS',
        },
        {
          type: 'docSidebar',
          sidebarId: 'advancedSidebar',
          position: 'left',
          label: 'Advanced',
        },
        {
          type: 'docSidebar',
          sidebarId: 'gettingStartedSidebar',
          position: 'left',
          label: 'Getting Started',
        },
        {
          href: 'https://github.com/jorge07/symfony-6-es-cqrs-boilerplate',
          label: 'GitHub',
          position: 'right',
        },
      ],
    },
    footer: {
      style: 'dark',
      links: [
        {
          title: 'Documentation',
          items: [
            {
              label: 'DDD Concepts',
              to: '/docs/ddd/intro',
            },
            {
              label: 'CQRS & Event Sourcing',
              to: '/docs/cqrs/intro',
            },
            {
              label: 'Advanced Topics',
              to: '/docs/advanced/intro',
            },
          ],
        },
        {
          title: 'Community',
          items: [
            {
              label: 'GitHub',
              href: 'https://github.com/jorge07/symfony-6-es-cqrs-boilerplate',
            },
            {
              label: 'Issues',
              href: 'https://github.com/jorge07/symfony-6-es-cqrs-boilerplate/issues',
            },
          ],
        },
        {
          title: 'More',
          items: [
            {
              label: 'Symfony',
              href: 'https://symfony.com',
            },
            {
              label: 'Broadway',
              href: 'https://github.com/broadway/broadway',
            },
          ],
        },
      ],
      copyright: `Copyright © ${new Date().getFullYear()} Jorge07. Built with Docusaurus.`,
    },
    prism: {
      theme: prismThemes.github,
      darkTheme: prismThemes.dracula,
      additionalLanguages: ['php', 'bash', 'yaml', 'json', 'sql'],
    },
  } satisfies Preset.ThemeConfig,
};

export default config;
