<?php

class pz_calendar_event_controller_api extends pz_controller
{
    public $name = 'calendar_event';
    public $function = '';
    public $functions = [];
    public $function_default = '';

    public function controller($function)
    {
        $format = rex_request('format', 'string', 'csv');

        if (pz::getUser()->isAdmin()) {
            $this->functions[] = 'jobs';
        }

        if (!in_array($function, $this->functions)) {
            $function = $this->function_default;
        }
        $this->function = $function;

        switch ($this->function) {
            case('jobs'):
                $return = $this->getJobs();
                break;

            default:
                $return = [];
        }
        return pz_api::send($return, $format);
    }

    private function getJobs()
    {
        $start = rex_request('from', 'int', '');
        $end = rex_request('to', 'int', '');

        if ($start == '' || !$from = DateTime::createFromFormat('Ymd', $start)) {
            $from = new DateTime();
        }

        if ($end == '' || !$to = DateTime::createFromFormat('Ymd', $end)) {
            $to = clone $from;
            $from->modify('-1 month');
        }

        $mode = rex_request('mode', 'string');
        if ($mode == 'all' && pz::getUser()->isAdmin()) {
            // all
            $user_ids = [];
            foreach (pz_user::getUsers() as $user) {
                $user_ids[] = $user->getId();
            }

            $project_ids = [];
            foreach (pz::getUser()->getProjects() as $p) {
                $project_ids[] = $p->getid();
            }

            $jobs = pz_calendar_event::getAll($project_ids, $from, $to, true, $user_ids);
        } else {
            // my jobs
            $project_ids = [];
            $projects = pz::getUser()->getMyProjects();
            foreach ($projects as $project) {
                $project_ids[] = $project->getId();
            }
            // $jobs = pz_calendar_event::getAll($project_ids, null, null, true, pz::getUser()->getId(), array('from'=>'desc'));
             $jobs = pz_calendar_event::getAll($project_ids, $from, $to, true, pz::getUser()->getId(), ['from' => 'desc']);
        }

        $return = [];
        foreach ($jobs as $job) {
            if ($e = new pz_calendar_event_api($job)) {
                $return[] = $e->getVars();
            }
        }

        return $return;
    }
}
