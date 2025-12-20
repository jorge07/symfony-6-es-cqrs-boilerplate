import type {SidebarsConfig} from '@docusaurus/plugin-content-docs';

// This runs in Node.js - Don't use client-side code here (browser APIs, JSX...)

/**
 * Creating a sidebar enables you to:
 - create an ordered group of docs
 - render a sidebar for each doc of that group
 - provide next/previous navigation

 The sidebars can be generated from the filesystem, or explicitly defined here.

 Create as many sidebars as you want.
 */
const sidebars: SidebarsConfig = {
  dddSidebar: [
    {
      type: 'category',
      label: 'Domain-Driven Design',
      items: [
        'ddd/intro',
        'ddd/objectives',
        'ddd/bounded-context',
        'ddd/value-objects-vs-entities',
        'ddd/aggregates',
        'ddd/layered-architecture',
        'ddd/anti-corruption-layer',
      ],
    },
  ],

  cqrsSidebar: [
    {
      type: 'category',
      label: 'CQRS & Event Sourcing',
      items: [
        'cqrs/intro',
        'cqrs/overview',
        'cqrs/command-bus',
        'cqrs/event-sourcing',
        'cqrs/application-workflow',
        'cqrs/symfony-messenger',
      ],
    },
  ],

  advancedSidebar: [
    {
      type: 'category',
      label: 'Advanced Topics',
      items: [
        'advanced/intro',
        'advanced/testing-strategies',
        'advanced/read-write-models',
        'advanced/event-sourcing-patterns',
      ],
    },
  ],

  gettingStartedSidebar: [
    {
      type: 'category',
      label: 'Getting Started',
      items: [
        'getting-started/buses',
        'getting-started/use-cases',
        'getting-started/projections',
        'getting-started/async',
        'getting-started/xdebug',
      ],
    },
  ],
};

export default sidebars;
