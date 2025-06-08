# eternal-combat
a self-running, cyber-cricket-fighting game that one may deploy on the website/blog; frontend accommodated in the style of Typecho Handsome theme template.

main dataflow:
1. generation of combat - combat_daemon.php (running as service)
1. data dumping - /eternalcombat/json/battle_state.json
1. frontend loading - battle.php

Running (or maybe not) example: [Battle - M.ICU](https://melokeo.icu/Battle)

Features:

- A whole bunch of weird attacks & statuses
- User interaction:
  - Crowd supports
  - Chaos if you'd prefer
