<?php

return [
    'first_response_hours' => (int) env('HIRING_FIRST_RESPONSE_HOURS', 72),
    'reminder_before_hours' => (int) env('HIRING_REMINDER_BEFORE_HOURS', 24),
    'job_confirmation_days' => (int) env('HIRING_JOB_CONFIRMATION_DAYS', 30),
    'active_responder_days' => (int) env('HIRING_ACTIVE_RESPONDER_DAYS', 14),
    'active_responder_rate' => (float) env('HIRING_ACTIVE_RESPONDER_RATE', 80),
];
