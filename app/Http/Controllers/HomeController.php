<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the public storefront landing page.
     */
    public function getIndex(): View
    {
        $categories = Category::with(['services' => function ($query) {
            $query->where('is_active', true)->limit(8);
        }])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $featuredServices = Service::with('category', 'provider')
            ->where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('rate', 'asc')
            ->limit(8)
            ->get();

        $howItWorks = [
            [
                'step' => 1,
                'title' => 'Choose a Service',
                'description' => 'Browse our categories and select the social media service you need.',
                'icon' => 'search',
            ],
            [
                'step' => 2,
                'title' => 'Place Your Order',
                'description' => 'Enter your link, specify the quantity, and complete the payment.',
                'icon' => 'shopping-cart',
            ],
            [
                'step' => 3,
                'title' => 'Watch Growth',
                'description' => 'Orders start processing immediately. Track progress in your dashboard.',
                'icon' => 'chart',
            ],
            [
                'step' => 4,
                'title' => 'Get Results',
                'description' => 'Enjoy increased engagement, followers, likes, views, or subscribers.',
                'icon' => 'trophy',
            ],
        ];



        $stats = [
            'orders_completed' => \App\Models\Order::where('status', 'completed')->count(),
            'happy_customers' => \App\Models\User::has('orders')->count(),
            'services_available' => Service::where('is_active', true)->count(),
            'support_tickets_resolved' => \App\Models\Ticket::where('status', 'closed')->count(),
        ];

        return view('home.index', compact(
            'categories',
            'featuredServices',
            'howItWorks',
            'stats'
        ));
    }

    /**
     * Display the public service listing page.
     */
    public function getServices(Request $request): View
    {
        $categories = Category::where('is_active', true)
            ->with(['services' => function ($query) {
                $query->where('is_active', true)->orderBy('rate', 'asc');
            }])
            ->orderBy('sort_order')
            ->get();

        $selectedCategory = $request->get('category');
        $search = $request->get('search');

        $query = Service::with('category', 'provider')
            ->where('is_active', true);

        if ($selectedCategory) {
            $query->where('category_id', $selectedCategory);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $services = $query->orderBy('category_id')
            ->orderBy('rate', 'asc')
            ->paginate(50);

        return view('home.services', compact('services', 'categories', 'selectedCategory', 'search'));
    }

    /**
     * Display a single service details.
     */
    public function getServiceDetails(string $id): View
    {
        $service = Service::with('category', 'provider')
            ->where('is_active', true)
            ->findOrFail($id);

        $relatedServices = Service::where('category_id', $service->category_id)
            ->where('id', '!=', $service->id)
            ->where('is_active', true)
            ->orderBy('rate', 'asc')
            ->limit(4)
            ->get();

        return view('home.service-details', compact('service', 'relatedServices'));
    }

    /**
     * Display pricing page.
     */
    public function getPricing(): View
    {
        $categories = Category::where('is_active', true)
            ->with(['services' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('rate', 'asc')
                    ->select('id', 'category_id', 'name', 'rate', 'min', 'max');
            }])
            ->orderBy('sort_order')
            ->get();

        return view('home.pricing', compact('categories'));
    }

    /**
     * Display about page.
     */
    public function getAbout(): View
    {
        $stats = [
            'orders_completed' => \App\Models\Order::where('status', 'completed')->count(),
            'happy_customers' => \App\Models\User::has('orders')->count(),
            'services_available' => Service::where('is_active', true)->count(),
            'average_rating' => 4.8,
        ];

        return view('home.about', compact('stats'));
    }

    /**
     * Display contact page.
     */
    public function getContact(): View
    {
        return view('home.contact');
    }

    /**
     * Submit contact form.
     */
    public function postContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        // Create a support ticket from contact form
        $ticket = \App\Models\Ticket::create([
            'user_id' => auth()->check() ? auth()->id() : null,
            'subject' => $validated['subject'],
            'priority' => 'medium',
            'status' => 'open',
        ]);

        \App\Models\TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->check() ? auth()->id() : null,
            'message' => "From: {$validated['name']} ({$validated['email']})\n\n{$validated['message']}",
            'is_admin' => false,
        ]);

        return redirect()->back()->with('success', 'Your message has been sent! We will get back to you soon.');
    }

    /**
     * Display terms of service page.
     */
    public function getTerms(): View
    {
        return view('home.terms');
    }

    /**
     * Display privacy policy page.
     */
    public function getPrivacy(): View
    {
        return view('home.privacy');
    }
}
