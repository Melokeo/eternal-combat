# eternal-combat
a self-running, cyber-cricket-fighting game that one may deploy on the website/blog; frontend accommodated in the style of Typecho Handsome theme template.

Running (or maybe not) example: [Battle - M.ICU](https://melokeo.icu/Battle)

Dependencies:
- PHP 8.0+ (for match, constructor, ?: syntax);
- json & session support;
- json write permisson to apache.

Setup with systemd.

Main dataflow:
1. generation of combat - combat_daemon.php (running as service)
1. data dumping - /eternalcombat/json/battle_state.json
1. frontend loading - battle.php

Features:

- A whole bunch of weird attacks & statuses
- Dynamic balancing based on battle history
- User interaction:
  - Crowd supports
  - Chaos if you'd prefer

![image](https://github.com/user-attachments/assets/bbd5728b-9d4d-43d7-9fa5-90ff21791113)
