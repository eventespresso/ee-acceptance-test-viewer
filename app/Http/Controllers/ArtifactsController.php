<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use App\Http\Helpers\TravisApi;

class ArtifactsController extends Controller
{
    /**
     * @var string  What is the common prefix for all the artifacts.
     */
    protected $prefix;


    /**
     * @var Filesystem
     */
    protected $storage;


    /**
     * @var TravisApi;
     */
    protected $travis;



    public function __construct(TravisApi $travis)
    {
        $this->prefix = env('AWS_ARTIFACT_PREFIX');
        $this->storage = Storage::disk('s3');
        $this->travis = $travis;
    }

    /**
     * Handler for "build" routes.
     * @param $id
     */
    public function locateBuild($build_id)
    {
        $template_arguments = $this->buildTemplateArguments($build_id);
        return view('viewer', $template_arguments);
    }


    /**
     * Handler for "job" routes
     *
     * @param $build_id
     * @param $job_id
     */
    public function locateJob($build_id, $job_id)
    {
        $template_arguments = $this->buildTemplateArguments($build_id, $job_id);
        return view('viewer', $template_arguments);
    }


    /**
     * Returns an array of template arguments for the view.
     * @param     $build_id
     * @param int $job_id
     */
    protected function buildTemplateArguments($build_id, $job_id = 0)
    {
        return [
            'title' => $this->getPageTitle($build_id, $job_id),
            'description' => $this->getPageDescription($build_id, $job_id),
            'jobs_list' => $job_id ? [] : $this->getBuildJobs($build_id),
            'artifacts' => $this->getArtifacts($build_id, $job_id),
        ];
    }


    protected function getBuildJobs($build_id)
    {
        $job_objects = $this->travis->getBuildJobObjects($build_id);
        //we just need the numbers for the jobs.  So we'll assemble that in an array where the keys are the numbers
        //and the values are the links.
        $jobs = [];
        foreach ($job_objects as $job) {
            $jobs[$job->number] = $this->getTravisLink($build_id, $job->id);
        }
        return $jobs;
    }


    protected function getArtifacts($build_id, $job_id = 0)
    {
        //first let's see if there are any artifacts at all for the build.
        if (! $this->storage->exists($this->path($build_id))) {
            return [];
        }

        //if we have a job_id, then let's just get the artifacts for that job
        if ($job_id) {
            $job = $this->travis->getJobObject($job_id);
            $artifacts = $this->prepFilesForTemplate($build_id, $job);
        } else {
            $artifacts = [];
            //get jobs
            $jobs = $this->travis->getBuildJobObjects($build_id);
            foreach ($jobs as $job) {
                $artifacts = array_merge($artifacts, $this->prepFilesForTemplate($build_id, $job));
            }
        }
        return $artifacts;
    }



    protected function prepFilesForTemplate($build_id, $job)
    {
        if (! $this->storage->exists($this->path($build_id, $job->id))) {
            return [];
        }
        $artifacts = [];
        $files = $this->storage->allFiles($this->path($build_id, $job->id));
        //prep for template
        foreach ($files as $file) {
            $url = $this->storage->url($file);
            if ($this->isImage($file)) {
                $artifacts[$job->number][] = '<img src="' . $url . '">';
            } else {
                $artifacts[$job->number][] = '<a href="' . $url . '">' . $file . '</a>';
            }
        }
        return $artifacts;
    }


    protected function getTravisLink($build_id, $job_id=0)
    {
        $prefix = 'https://travis-ci.org/eventespresso/ee-codeception/';
        return $job_id
            ? $prefix . 'jobs/' . $job_id
            : $prefix . 'builds/' . $build_id;
    }



    protected function getPageTitle($build_id, $job_id = 0)
    {
        $build_object = $this->travis->getBuildObject($build_id);
        if ($job_id) {
            $job_object = $this->travis->getJobObject($job_id);
            $title = sprintf(
                'Viewing Artifacts for %1$sBuild %2$s, Job %3$s%4$s.',
                '<a href="' . $this->getTravisLink($build_id, $job_id) . '">',
                $build_object->number,
                $job_object->number,
                '</a>'
            );
        } else {
            $title = sprintf(
                'Viewing Artifacts for %1$sBuild %2$s%3$s',
                '<a href="' . $this->getTravisLink($build_id, $job_id) . '">',
                $build_object->number,
                '</a>'
            );
        }
        return $title;
    }



    protected function getPageDescription($build_id, $job_id = 0)
    {
        $build_object = $this->travis->getBuildObject($build_id);
        return $build_object->commit->message;
    }



    protected function path($build_id, $job_id = 0)
    {
        $path = $this->prefix . $build_id;
        if ($job_id) {
            $path .= '/' . $job_id;
        }
        return $path;
    }


    protected function isImage($file)
    {
        return strpos($file, '.png') !== false;
    }
}