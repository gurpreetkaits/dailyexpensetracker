<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [ 'name' => 'Salary',            'type' => 'income',  'color' => '#FFF9C4', 'icon' => 'salary' ],
            [ 'name' => 'Business',          'type' => 'income',  'color' => '#F6D9BE', 'icon' => 'business' ],
            [ 'name' => 'Investments',       'type' => 'income',  'color' => '#C8E6C9', 'icon' => 'investment' ],
            [ 'name' => 'Extra Income',      'type' => 'income',  'color' => '#FFF9C4', 'icon' => 'extraincome' ],
            [ 'name' => 'Loan',              'type' => 'income',  'color' => '#E1F5FE', 'icon' => 'loan' ],
            [ 'name' => 'Parental Leave',    'type' => 'income',  'color' => '#FCE4EC', 'icon' => 'parentalleave' ],
            [ 'name' => 'Insurance Payout',  'type' => 'income',  'color' => '#E1F5FE', 'icon' => 'insurancepayout' ],
            [ 'name' => 'Other',             'type' => 'income',  'color' => '#F5F5F5', 'icon' => 'other' ],

            // ── Expense Core ───────────────────────────────────────────
            [ 'name' => 'Food & Drink',        'type' => 'expense', 'color' => '#FFF3E0', 'icon' => 'foodanddrink' ],
            [ 'name' => 'Shopping',            'type' => 'expense', 'color' => '#F3E5F5', 'icon' => 'shopping' ],
            [ 'name' => 'Transport',           'type' => 'expense', 'color' => '#E3F2FD', 'icon' => 'transport' ],
            [ 'name' => 'Home',                'type' => 'expense', 'color' => '#FFECB3', 'icon' => 'home' ],
            [ 'name' => 'Bills & Fees',        'type' => 'expense', 'color' => '#E0F7FA', 'icon' => 'billsandfees' ],
            [ 'name' => 'Entertainment',       'type' => 'expense', 'color' => '#F8BBD0', 'icon' => 'entertainment' ],
            [ 'name' => 'Car',                 'type' => 'expense', 'color' => '#E3F2FD', 'icon' => 'car' ],
            [ 'name' => 'Travel',              'type' => 'expense', 'color' => '#E1F5FE', 'icon' => 'travel' ],
            [ 'name' => 'Family & Personal',   'type' => 'expense', 'color' => '#FFFDE7', 'icon' => 'familyandpersonal' ],
            [ 'name' => 'Healthcare',          'type' => 'expense', 'color' => '#E1F5FE', 'icon' => 'healthcare' ],
            [ 'name' => 'Education',           'type' => 'expense', 'color' => '#FFF9C4', 'icon' => 'education' ],
            [ 'name' => 'Groceries',           'type' => 'expense', 'color' => '#E8F5E9', 'icon' => 'groceries' ],
            [ 'name' => 'Gifts',               'type' => 'expense', 'color' => '#FCE4EC', 'icon' => 'gifts' ],
            [ 'name' => 'Sport & Hobbies',     'type' => 'expense', 'color' => '#E0F7FA', 'icon' => 'sportandhobbies' ],
            [ 'name' => 'Beauty',              'type' => 'expense', 'color' => '#F3E5F5', 'icon' => 'beauty' ],
            [ 'name' => 'Work',                'type' => 'expense', 'color' => '#E3F2FD', 'icon' => 'work' ],
            [ 'name' => 'Other',               'type' => 'expense', 'color' => '#F5F5F5', 'icon' => 'other' ],

            // ── Expense Synonyms / Additional ─────────────────────────
            [ 'name' => 'Food',                'type' => 'expense', 'color' => '#FFF3E0', 'icon' => 'food' ],
            [ 'name' => 'Drink',               'type' => 'expense', 'color' => '#FFF3E0', 'icon' => 'drink' ],
            [ 'name' => 'Restaurant',          'type' => 'expense', 'color' => '#FFF3E0', 'icon' => 'restaurant' ],
            [ 'name' => 'Cafe',                'type' => 'expense', 'color' => '#FFF3E0', 'icon' => 'cafe' ],
            [ 'name' => 'Coffee',              'type' => 'expense', 'color' => '#FFF3E0', 'icon' => 'coffee' ],
            [ 'name' => 'Movie',               'type' => 'expense', 'color' => '#F8BBD0', 'icon' => 'movie' ],
            [ 'name' => 'Cinema',              'type' => 'expense', 'color' => '#F8BBD0', 'icon' => 'cinema' ],
            [ 'name' => 'Games',               'type' => 'expense', 'color' => '#D1C4E9', 'icon' => 'games' ],
            [ 'name' => 'Gaming',              'type' => 'expense', 'color' => '#D1C4E9', 'icon' => 'gaming' ],
            [ 'name' => 'Music',               'type' => 'expense', 'color' => '#E0F7FA', 'icon' => 'music' ],
            [ 'name' => 'Gym',                 'type' => 'expense', 'color' => '#E0F7FA', 'icon' => 'gym' ],
            [ 'name' => 'Fitness',             'type' => 'expense', 'color' => '#E0F7FA', 'icon' => 'fitness' ],
            [ 'name' => 'Sports',              'type' => 'expense', 'color' => '#E0F7FA', 'icon' => 'sports' ],
            [ 'name' => 'Medical',             'type' => 'expense', 'color' => '#E1F5FE', 'icon' => 'medical' ],
            [ 'name' => 'Health',              'type' => 'expense', 'color' => '#E1F5FE', 'icon' => 'health' ],
            [ 'name' => 'School',              'type' => 'expense', 'color' => '#FFF9C4', 'icon' => 'school' ],
            [ 'name' => 'University',          'type' => 'expense', 'color' => '#FFF9C4', 'icon' => 'university' ],
            [ 'name' => 'Study',               'type' => 'expense', 'color' => '#FFF9C4', 'icon' => 'study' ],
            [ 'name' => 'Books',               'type' => 'expense', 'color' => '#FFF9C4', 'icon' => 'books' ],
            [ 'name' => 'Clothes',             'type' => 'expense', 'color' => '#F3E5F5', 'icon' => 'clothes' ],
            [ 'name' => 'Fashion',             'type' => 'expense', 'color' => '#F3E5F5', 'icon' => 'fashion' ],
            [ 'name' => 'Cosmetics',           'type' => 'expense', 'color' => '#F3E5F5', 'icon' => 'cosmetics' ],
            [ 'name' => 'Salon',               'type' => 'expense', 'color' => '#F3E5F5', 'icon' => 'salon' ],
            [ 'name' => 'Haircut',             'type' => 'expense', 'color' => '#F3E5F5', 'icon' => 'haircut' ],
            [ 'name' => 'Taxi',                'type' => 'expense', 'color' => '#E3F2FD', 'icon' => 'taxi' ],
            [ 'name' => 'Uber',                'type' => 'expense', 'color' => '#E3F2FD', 'icon' => 'uber' ],
            [ 'name' => 'Bus',                 'type' => 'expense', 'color' => '#E3F2FD', 'icon' => 'bus' ],
            [ 'name' => 'Train',               'type' => 'expense', 'color' => '#E3F2FD', 'icon' => 'train' ],
            [ 'name' => 'Metro',               'type' => 'expense', 'color' => '#E3F2FD', 'icon' => 'metro' ],
            [ 'name' => 'Subway',              'type' => 'expense', 'color' => '#E3F2FD', 'icon' => 'subway' ],
            [ 'name' => 'Plane',               'type' => 'expense', 'color' => '#E1F5FE', 'icon' => 'plane' ],
            [ 'name' => 'Flight',              'type' => 'expense', 'color' => '#E1F5FE', 'icon' => 'flight' ],
            [ 'name' => 'Vacation',            'type' => 'expense', 'color' => '#E1F5FE', 'icon' => 'vacation' ],
            [ 'name' => 'Holiday',             'type' => 'expense', 'color' => '#E1F5FE', 'icon' => 'holiday' ],
            [ 'name' => 'Hotel',               'type' => 'expense', 'color' => '#E1F5FE', 'icon' => 'hotel' ],
            [ 'name' => 'Accommodation',       'type' => 'expense', 'color' => '#E1F5FE', 'icon' => 'accommodation' ],
            [ 'name' => 'Rent',                'type' => 'expense', 'color' => '#FFECB3', 'icon' => 'rent' ],
            [ 'name' => 'Mortgage',            'type' => 'expense', 'color' => '#FFECB3', 'icon' => 'mortgage' ],
            [ 'name' => 'Utilities',           'type' => 'expense', 'color' => '#E0F7FA', 'icon' => 'utilities' ],
            [ 'name' => 'Electricity',         'type' => 'expense', 'color' => '#FFFDE7', 'icon' => 'electricity' ],
            [ 'name' => 'Water',               'type' => 'expense', 'color' => '#E1F5FE', 'icon' => 'water' ],
            [ 'name' => 'Gas',                 'type' => 'expense', 'color' => '#FFECB3', 'icon' => 'gas' ],
            [ 'name' => 'Internet',            'type' => 'expense', 'color' => '#E0F7FA', 'icon' => 'internet' ],
            [ 'name' => 'Wifi',                'type' => 'expense', 'color' => '#E0F7FA', 'icon' => 'wifi' ],
            [ 'name' => 'Phone',               'type' => 'expense', 'color' => '#E0F7FA', 'icon' => 'phone' ],
            [ 'name' => 'Mobile',              'type' => 'expense', 'color' => '#E0F7FA', 'icon' => 'mobile' ],
            [ 'name' => 'Subscription',        'type' => 'expense', 'color' => '#E0F7FA', 'icon' => 'subscription' ],
            [ 'name' => 'Streaming',           'type' => 'expense', 'color' => '#F8BBD0', 'icon' => 'streaming' ],
            [ 'name' => 'Netflix',             'type' => 'expense', 'color' => '#F8BBD0', 'icon' => 'netflix' ],
            [ 'name' => 'Spotify',             'type' => 'expense', 'color' => '#E0F7FA', 'icon' => 'spotify' ],
            [ 'name' => 'Amazon',              'type' => 'expense', 'color' => '#F3E5F5', 'icon' => 'amazon' ],
            [ 'name' => 'Online',              'type' => 'expense', 'color' => '#F3E5F5', 'icon' => 'online' ],
            [ 'name' => 'Delivery',            'type' => 'expense', 'color' => '#E0F7FA', 'icon' => 'delivery' ],
            [ 'name' => 'Takeout',             'type' => 'expense', 'color' => '#FFF3E0', 'icon' => 'takeout' ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => strtolower($category['name'])],
                $category
            );
        }
    }
}
