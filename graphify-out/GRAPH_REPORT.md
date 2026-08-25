# Graph Report - .  (2026-08-21)

## Corpus Check
- Corpus is ~7,608 words - fits in a single context window. You may not need a graph.

## Summary
- 73 nodes · 101 edges · 12 communities (8 shown, 4 thin omitted)
- Extraction: 88% EXTRACTED · 12% INFERRED · 0% AMBIGUOUS · INFERRED: 12 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- REST API & Routers
- API Services & Config
- Base Data Models
- Composer Metadata
- Database Migrations
- Leads Management Model
- Admin Navigation & Views

## God Nodes (most connected - your core abstractions)
1. `MainApiRouter` - 14 edges
2. `WhizSimpleLeadModel` - 10 edges
3. `WhizApiConfigModel` - 9 edges
4. `WhizModels` - 9 edges
5. `WhizApiService` - 6 edges
6. `WhizMigrationCore` - 5 edges
7. `WhizCurlService` - 5 edges
8. `WhizViews` - 5 edges
9. `autoload` - 2 edges
10. `psr-4` - 2 edges

## Surprising Connections (you probably didn't know these)
- `WhizApiConfigModel` --inherits--> `WhizModels`  [EXTRACTED]
  WhizApi/Models/WhizApiConfigModel.php → WhizApi/Models/WhizModels.php
- `WhizSimpleLeadModel` --inherits--> `WhizModels`  [EXTRACTED]
  WhizApi/Models/WhizSimpleLeadModel.php → WhizApi/Models/WhizModels.php

## Import Cycles
- None detected.

## Communities (12 total, 4 thin omitted)

### Community 0 - "REST API & Routers"
Cohesion: 0.22
Nodes (4): wp_mail(), MainApiRouter, WP_REST_Request, WP_REST_Response

### Community 1 - "API Services & Config"
Cohesion: 0.18
Nodes (3): WhizApiConfigModel, WhizApiService, WhizCurlService

### Community 4 - "Composer Metadata"
Cohesion: 0.29
Nodes (6): authors, autoload, psr-4, name, WhizApi\\, require

## Knowledge Gaps
- **4 isolated node(s):** `name`, `authors`, `require`, `WhizApi\\`
  These have ≤1 connection - possible missing edges or undocumented components.
- **4 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `WhizApiConfigModel` connect `API Services & Config` to `REST API & Routers`, `Plugin Entry & Overrides`, `Base Data Models`, `Admin Navigation & Views`?**
  _High betweenness centrality (0.231) - this node is a cross-community bridge._
- **Why does `WhizSimpleLeadModel` connect `Leads Management Model` to `REST API & Routers`, `Plugin Entry & Overrides`, `Base Data Models`, `Admin Navigation & Views`?**
  _High betweenness centrality (0.183) - this node is a cross-community bridge._
- **Why does `WhizModels` connect `Base Data Models` to `API Services & Config`, `Leads Management Model`?**
  _High betweenness centrality (0.173) - this node is a cross-community bridge._
- **Are the 5 inferred relationships involving `WhizSimpleLeadModel` (e.g. with `.api_json()` and `.get_lead_details()`) actually correct?**
  _`WhizSimpleLeadModel` has 5 INFERRED edges - model-reasoned connections that need verification._
- **Are the 4 inferred relationships involving `WhizApiConfigModel` (e.g. with `.save_settings()` and `.__construct()`) actually correct?**
  _`WhizApiConfigModel` has 4 INFERRED edges - model-reasoned connections that need verification._
- **What connects `name`, `authors`, `require` to the rest of the system?**
  _4 weakly-connected nodes found - possible documentation gaps or missing edges._