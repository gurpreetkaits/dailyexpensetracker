@extends('layouts.app')

@section('content')
<article class="min-h-screen bg-white">
    <!-- Hero/Header -->
    <header class="bg-emerald-50 py-16 mb-12">
        <div class="max-w-3xl mx-auto px-4">
            <div class="flex items-center gap-2 text-sm text-emerald-600 mb-4">
                <a href="/posts" class="hover:text-emerald-700 inline-flex items-center gap-1">← Back to Blog</a>
                <span class="text-gray-400">•</span>
                <time>{{ $post['date'] }}</time>
            </div>
            
            <h1 class="text-4xl font-bold text-gray-900 mb-4">
                {{ $post['title'] }}
            </h1>
            
            <p class="text-lg text-gray-600">
                {{ $post['description'] }}
            </p>
        </div>
    </header>

    <!-- Content with custom styling -->
    <div class="max-w-3xl mx-auto px-4 pb-16">
        <div class="blog-content">
            {!! $post['content'] !!}
        </div>
        
        <div class="mt-16 bg-emerald-50 rounded-2xl p-8 text-center">
            <h3 class="text-2xl font-bold text-gray-900 mb-4">Ready to Start Tracking?</h3>
            <p class="text-gray-600 mb-6">Join many other users who are mastering their finances with Daily Expense Tracker.</p>
            <a href="{{ config('app.frontend_url') }}/login" 
               class="inline-block bg-emerald-500 text-white px-8 py-3 rounded-lg hover:bg-emerald-600 transition-colors">
                Start Tracking Free
            </a>
        </div>
    </div>
</article>

<style>
    /* Custom blog content styling */
    .blog-content {
        color: #374151;
        line-height: 1.8;
    }
    
    .blog-content h1 {
        font-size: 2.25rem;
        font-weight: 700;
        margin: 2rem 0 1rem;
    }
    
    .blog-content h2 {
        font-size: 1.875rem;
        font-weight: 600;
        margin: 2rem 0 1rem;
        color: #111827;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 0.5rem;
    }
    
    .blog-content h3 {
        font-size: 1.5rem;
        font-weight: 600;
        margin: 1.5rem 0 1rem;
    }
    
    .blog-content p {
        margin-bottom: 1.25rem;
        color: #4b5563;
    }
    
    .blog-content ul {
        list-style-type: disc;
        margin: 1.25rem 0;
        padding-left: 1.5rem;
    }
    
    .blog-content li {
        margin: 0.5rem 0;
        color: #4b5563;
    }
    
    .blog-content strong {
        font-weight: 600;
        color: #111827;
    }
    
    .blog-content blockquote {
        border-left: 4px solid #10b981;
        background: #f9fafb;
        padding: 1rem 1.5rem;
        margin: 1.5rem 0;
        font-style: italic;
    }
    
    .blog-content a {
        color: #059669;
        text-decoration: underline;
    }
    
    .blog-content a:hover {
        color: #047857;
    }
    
    .blog-content code {
        background: #f3f4f6;
        padding: 0.2rem 0.4rem;
        border-radius: 0.25rem;
        font-family: monospace;
    }

    /* For bullet points that might be deep */
    .blog-content ul ul {
        margin-left: 1.5rem;
    }
</style>
@endsection