# architecture-rulebook

[architecture-rulebook](https://github.com/ianflanagan1/architecture-rulebook) imports `rulebook-coverage.json` with `task import`, adds the version and checks it against `schema/coverage.schema.json`. A listing the schema rejects can't be imported.

| What | Where in the rulebook | Coupling |
|---|---|---|
| The listing's fields, check kinds and coverage levels | `schema/coverage.schema.json` | COUPLED:coverage-listing-shape. = architecture-rulebook:schema/coverage.schema.json |
| The clause IDs the listing names | `rulebook/rules/*.yaml` | A clause the rulebook doesn't have fails the import's check |
