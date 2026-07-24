# ProctorCore Quiz access rule - release 0.10.0

Technical component: `quizaccess_proctorcore`  
Install location: `mod/quiz/accessrule/proctorcore`

This companion plugin connects Moodle Quiz attempts to `local_proctorcore`.

Implemented behaviour:

- Section 5.1 browser/device preflight and admin preview;
- Section 1.2 identity/liveness gate before Moodle creates the Quiz attempt;
- Section 1.1 camera/microphone capture on the real attempt page;
- Section 1.3 continuous sampled behaviour monitoring and browser-event violations;
- Section 3.1 Quiz-level Proctoring reports button;
- Section 5.3 heartbeat and same-attempt reconnect;
- Moodle's native Quiz timer and answer persistence remain authoritative.

Dependency: `local_proctorcore` version `2026072003` or later.
