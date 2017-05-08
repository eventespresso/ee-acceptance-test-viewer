## EE Acceptance Test Viewer

This is a simple app built with Laravel to enable viewing of acceptance test artifacts uploaded to an Amazon S3 bucket via travis.

### Installation

Set things up via composer:

```bash
composer install --no-dev
```

Generate an app key:

```bash
php artisan key:generate
```

Copy `.env.example` to `.env` and add your generated `APP_KEY` there as well as values for the other items.

Make sure you have the value for `APP_URL` setup on your webserver and pointing to `/public/index.php`.

### Usage

This is just a viewer, so there is no menu, and no user discovery process for artifacts.  The following assumption currently exists for the app:

The artifacts are uploaded to your S3 bucket in the following structure:

> {AWS_BUCKET}/{AWS_PREFIX}/{build_id}/{job_id}

**build_id** is equivalent to the identifier for the build in travis _not_ the build number (same with **job_id**).

With that assumption, (and assuming the value for `APP_URL` is http://my-viewer.com), you could view all artifacts for a build at:

> http://my-viewer.com/build/122345

And that will check your s3 bucket for any artifacts for that build id.

You can do something similar for jobs:
 
 > http://my-viewer.com/build/122345/job/23456
 
 That will automatically check the s3 bucket for artifacts for that specific job.
 

