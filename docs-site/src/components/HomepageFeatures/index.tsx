import type {ReactNode} from 'react';
import clsx from 'clsx';
import Heading from '@theme/Heading';
import styles from './styles.module.css';

type FeatureItem = {
  title: string;
  emoji: string;
  description: ReactNode;
};

const FeatureList: FeatureItem[] = [
  {
    title: 'Domain-Driven Design',
    emoji: '🏗️',
    description: (
      <>
        Learn DDD concepts including Bounded Contexts, Value Objects, Entities, 
        Aggregates, and Layered Architecture with practical examples from the User domain.
      </>
    ),
  },
  {
    title: 'CQRS & Event Sourcing',
    emoji: '⚡',
    description: (
      <>
        Understand Command Query Responsibility Segregation and Event Sourcing patterns
        with Symfony Messenger integration and practical implementation examples.
      </>
    ),
  },
  {
    title: 'Production Ready',
    emoji: '🚀',
    description: (
      <>
        Advanced patterns for testing, monitoring, and scaling your DDD/CQRS application
        with real-world best practices and performance optimization strategies.
      </>
    ),
  },
];

function Feature({title, emoji, description}: FeatureItem) {
  return (
    <div className={clsx('col col--4')}>
      <div className="text--center" style={{fontSize: '4rem'}}>
        {emoji}
      </div>
      <div className="text--center padding-horiz--md">
        <Heading as="h3">{title}</Heading>
        <p>{description}</p>
      </div>
    </div>
  );
}

export default function HomepageFeatures(): ReactNode {
  return (
    <section className={styles.features}>
      <div className="container">
        <div className="row">
          {FeatureList.map((props, idx) => (
            <Feature key={idx} {...props} />
          ))}
        </div>
      </div>
    </section>
  );
}
