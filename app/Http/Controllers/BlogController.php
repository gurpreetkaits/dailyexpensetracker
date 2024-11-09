<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Support\Facades\File;

class BlogController extends Controller
{
    public function index()
    {
        $posts = collect(File::files(public_path('blog/posts')))
            ->map(function ($file) {
                $content = File::get($file->getPathname());
                $metadata = $this->getMetadata($content);
                return [
                    'slug' => str_replace('.md', '', $file->getFilename()),
                    'title' => $metadata['title'] ?? '',
                    'description' => $metadata['description'] ?? '',
                    'date' => $metadata['date'] ?? '',
                ];
            })
            ->sortByDesc('date');

        return view('blog.index', compact('posts'));
    }

    public function show($slug)
    {
        $content = File::get(public_path(path: "blog/posts/{$slug}.md"));
        $metadata = $this->getMetadata($content);
        $html = Markdown::convertToHtml($content);

        return view('blog.post', [
            'post' => [
                'title' => $metadata['title'] ?? '',
                'content' => $html,
                'date' => $metadata['date'] ?? '',
                'description' => $metadata['description'] ?? ''
            ]
        ]);
    }

    private function getMetadata($content)
    {
        preg_match('/<!--(.*?)-->/s', $content, $matches);
        if (isset($matches[1])) {
            $lines = explode("\n", $matches[1]);
            $metadata = [];
            foreach ($lines as $line) {
                if (strpos($line, ':') !== false) {
                    [$key, $value] = explode(':', $line, 2);
                    $metadata[trim($key)] = trim($value);
                }
            }
            return $metadata;
        }
        return [];
    }
}