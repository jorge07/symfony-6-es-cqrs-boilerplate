# Documentation Site

This directory contains the Docusaurus site for the Symfony 6 ES CQRS Boilerplate documentation.

## Development

### Installation

```bash
cd docs-site
npm install
```

### Local Development

```bash
npm start
```

This command starts a local development server and opens up a browser window. Most changes are reflected live without having to restart the server.

### Build

```bash
npm run build
```

This command generates static content into the `build` directory and can be served using any static contents hosting service.

### Deployment

The documentation is automatically deployed to GitHub Pages when changes are pushed to the `master` branch.

You can also manually deploy using:

```bash
GIT_USER=<Your GitHub username> npm run deploy
```

## Structure

The documentation is organized into the following sections:

- **DDD** - Domain-Driven Design concepts and patterns
- **CQRS** - Command Query Responsibility Segregation and Event Sourcing
- **Advanced** - Advanced topics including testing and performance optimization
- **Getting Started** - Quick start guides and tutorials

## Updating Documentation

1. Make changes to the markdown files in the `../doc` directory
2. Run the documentation sync script to update the Docusaurus docs
3. Test locally with `npm start`
4. Commit and push changes - GitHub Actions will automatically deploy
