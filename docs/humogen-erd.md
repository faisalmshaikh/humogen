# Humogen database ERD

This document is a Mermaid-native entity relationship diagram derived from the supplied phpMyAdmin SQL dump for `khandesh21at_humogen`.

The solid relationships below are the five foreign keys declared in the dump. The schema also contains many logical relationships based on fields such as `*_tree_id`, `*_gedcomnr`, and `*_connect_id`; those are documented separately because MariaDB does not enforce them as foreign keys.

## Enforced relational core

```mermaid
erDiagram
    humo_trees {
        SMALLINT tree_id
        SMALLINT tree_order
        VARCHAR tree_prefix
        DATE tree_date
        INT tree_persons
        INT tree_families
        VARCHAR tree_owner
        VARCHAR tree_privacy
    }

    humo_persons {
        INT pers_id PK
        VARCHAR pers_gedcomnumber
        MEDIUMINT pers_tree_id
        VARCHAR pers_firstname
        VARCHAR pers_lastname
        VARCHAR pers_sexe
        VARCHAR pers_alive
        VARCHAR pers_religion
    }

    humo_families {
        INT fam_id PK
        MEDIUMINT fam_tree_id
        VARCHAR fam_gedcomnumber
        VARCHAR fam_kind
        VARCHAR fam_religion
        INT fam_alive
    }

    humo_events {
        INT event_id PK
        SMALLINT event_tree_id
        VARCHAR event_gedcomnr
        INT person_id FK
        INT relation_id FK
        INT place_id FK
        VARCHAR event_kind
        VARCHAR event_date
        VARCHAR event_connect_id
    }

    humo_location {
        INT location_id PK
        VARCHAR location_location
        FLOAT location_lat
        FLOAT location_lng
        TEXT location_status
    }

    humo_relations_persons {
        INT id PK
        INT relation_id FK
        INT person_id FK
        VARCHAR relation_gedcomnumber
        VARCHAR person_gedcomnumber
        MEDIUMINT tree_id
        VARCHAR relation_type
        MEDIUMINT relation_order
    }

    humo_persons ||--o{ humo_events : "person_id / SET NULL"
    humo_families ||--o{ humo_events : "relation_id / SET NULL"
    humo_location ||--o{ humo_events : "place_id / SET NULL"
    humo_persons ||--o{ humo_relations_persons : "person_id / CASCADE"
    humo_families ||--o{ humo_relations_persons : "relation_id / CASCADE"
```





## Logical genealogy and application links

These links are visible from column names and indexes, but are not declared as foreign keys in the dump. They are shown as logical associations rather than enforced constraints.

```mermaid
flowchart LR
    T["humo_trees\n(tree_id)"]
    P["humo_persons\n(pers_id, pers_tree_id, pers_gedcomnumber)"]
    F["humo_families\n(fam_id, fam_tree_id, fam_gedcomnumber)"]
    E["humo_events\n(event_tree_id, event_gedcomnr, event_connect_id)"]
    A["humo_addresses\n(address_tree_id, address_gedcomnr, address_connect_id)"]
    C["humo_connections\n(connect_tree_id, connect_connect_id, connect_source_id)"]
    S["humo_sources\n(source_tree_id, source_gedcomnr, source_repo_gedcomnr)"]
    R["humo_repositories\n(repo_tree_id, repo_gedcomnr)"]
    X["humo_texts\n(text_tree_id, text_gedcomnr)"]
    N["humo_user_notes\n(note_tree_id, note_connect_id)"]
    L["humo_location\n(location_id)"]
    U["humo_users\n(user_id, user_group_id)"]
    G["humo_groups\n(group_id)"]
    TT["humo_tree_texts\n(treetext_tree_id)"]
    ST["humo_settings\n(setting_tree_id)"]

    T -. "tree_id" .-> P
    T -. "tree_id" .-> F
    T -. "tree_id" .-> E
    T -. "tree_id" .-> A
    T -. "tree_id" .-> C
    T -. "tree_id" .-> S
    T -. "tree_id" .-> R
    T -. "tree_id" .-> X
    T -. "tree_id" .-> N
    T -. "tree_id" .-> TT
    T -. "tree_id" .-> ST
    P -. "GEDCOM number" .-> E
    P -. "GEDCOM number" .-> A
    P -. "GEDCOM number" .-> X
    F -. "GEDCOM number" .-> E
    S -. "repository GEDCOM number" .-> R
    U -. "user_group_id" .-> G
    E -. "place_id (indexed, not FK)" .-> L
```





## Auxiliary, staging, survey, and reporting objects

The following objects are present in the dump but have no declared foreign-key constraints. Many are denormalized imports, temporary staging tables, materialized relationship outputs, or application-specific extensions.

```mermaid
flowchart TB
    CORE["Core genealogy tables"]
    STAGE["Import / staging"]
    LEGACY["Legacy or custom tables"]
    REPORT["Derived relationship/report tables"]
    SURVEY["Survey tables"]

    CORE --> STAGE
    CORE --> REPORT
    SURVEY --> REPORT

    STAGE --- P1["humo_persons_temp"]
    STAGE --- F1["humo_families_temp"]
    STAGE --- E1["humo_events_temp"]

    LEGACY --- B["bachelors2"]
    LEGACY --- I["Individuals"]
    LEGACY --- M["markers"]
    LEGACY --- MP["missingphone"]
    LEGACY --- SP["spouse"]
    LEGACY --- XD["xx_pers_details"]
    LEGACY --- EDU["Education"]

    SURVEY --- DS["x_datasurvey"]
    SURVEY --- SH["x_field_survey_hdr"]
    SURVEY --- SD["x_field_survey_dtl"]
    SURVEY --- SDD["x_field_survey_data"]

    REPORT --- VR["v_relatives / v_relatives_info"]
    REPORT --- VP["v_getphones_city / v_get_dob"]
    REPORT --- VF["v_father / v_mother / v_spouse"]
    REPORT --- VS["v_son / v_daughter / v_sister / v_brother"]
    REPORT --- VG["v_pat_* / v_mat_* / v_*_brother / v_*_sister"]
    REPORT --- VC["v_*_sp / v_*_son / v_*_daughter"]
```





### Interpretation notes

- `humo_events.person_id`, `humo_events.relation_id`, and `humo_events.place_id` are nullable and use `ON DELETE SET NULL`.
- `humo_relations_persons.person_id` and `humo_relations_persons.relation_id` use `ON DELETE CASCADE`.
- The `humo_trees` table has no primary-key declaration in the supplied dump, although `tree_id` is used throughout the schema as the tree discriminator.
- Tables prefixed `v_` are created as tables in this dump, not SQL views; their names indicate denormalized relationship outputs.
- Mermaid renders the code blocks directly in GitHub-compatible Markdown viewers and in applications that load Mermaid.js.

