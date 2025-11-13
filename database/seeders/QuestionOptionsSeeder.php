<?php

namespace Database\Seeders;

use App\Models\QuestionsOption;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuestionOptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $questionWhoIsMovingId = 1;
        $questionGenderId = 4;
        $questionSexualityId = 5;
        $employmentQuestionId = 6;
        $drinkQuestionId = 7;
        $smokeQuestionId = 8;
        $dietQuestionId  = 9;
        $ethnicityQuestionId = 18;
        $openToTeamUps = 19;
        $preferredStayLengthQuestionId = 21;
        $rentalHistory = 24;
        $propertyPreferenceQuestionId = 25;
        $languagesSpeakQuestionId = 26;
        $politicalViewsQuestionId = 27;
        $openToOvernightGuestsQuestionId = 28;
        $petsQuestionId = 29;
        $interestsQuestionId = 30;
        $religionQuestionId = 31;

        // For Property
        $accommodationTypeQuestionId = 56;
        $totalBedroomsQuestionId = 58;
        $totalBathroomsQuestionId = 59;
        $parkingAvailableQuestionId = 60;
        $wifiQuestionId = 61;
        $homeAccessibilityQuestionId = 62;
        $propertyFacilitiesQuestionId = 63;
        $peopleLivingQuestionId = 64;
        $roomTypeQuestionId = 66;
        $roomFurnishingsQuestionId = 67;
        $bathroomQuestionId = 68;
        $bedSizeQuestionId = 69;
        $roomFeaturesQuestionId = 70;
        $billIncludedQuestionId = 72; 
        $minStayQuestionId = 76; 
        $maxStayQuestionId = 77; 
        $housematePrefQuestionId = 78;
        $acceptingOfQuestionId = 79; 

        $propertyPreferenceInternetQuestionId = 82;
        $propertyPreferenceBathroomQuestionId = 83;
        $propertyPreferenceParkingQuestionId = 84;
        $propertyPreferenceMaxRoommatesQuestionId = 85;
        $whatGenderBestDescribesYou = 90;
        $ethnicity = 91;

        $optionIconPath = 'images/options/';

        $data = [

            // Who is moving
            ['question_id' => $questionWhoIsMovingId, 'label_for_app' => 'Just me', 'label_for_web' => 'Just me'],
            ['question_id' => $questionWhoIsMovingId, 'label_for_app' => 'Me and my partner', 'label_for_web' => 'Me and my partner'],

            // Gender options
            ['question_id' => $questionGenderId, 'label_for_app' => 'Woman', 'label_for_web' => 'Woman'],
            ['question_id' => $questionGenderId, 'label_for_app' => 'Man', 'label_for_web' => 'Man'],
            ['question_id' => $questionGenderId, 'label_for_app' => 'Non-binary', 'label_for_web' => 'Non-binary'],
            ['question_id' => $questionGenderId, 'label_for_app' => 'Trans-woman', 'label_for_web' => 'Trans-woman'],
            ['question_id' => $questionGenderId, 'label_for_app' => 'Trans-man', 'label_for_web' => 'Trans-man'],

            // Sexuality options
            ['question_id' => $questionSexualityId, 'label_for_app' => 'Straight', 'label_for_web' => 'Straight'],
            ['question_id' => $questionSexualityId, 'label_for_app' => 'Bisexual', 'label_for_web' => 'Bisexual'],
            ['question_id' => $questionSexualityId, 'label_for_app' => 'Gay', 'label_for_web' => 'Gay'],
            ['question_id' => $questionSexualityId, 'label_for_app' => 'Pansexual', 'label_for_web' => 'Pansexual'],
            ['question_id' => $questionSexualityId, 'label_for_app' => 'Lesbian', 'label_for_web' => 'Lesbian'],
            ['question_id' => $questionSexualityId, 'label_for_app' => 'Queer', 'label_for_web' => 'Queer'],
            ['question_id' => $questionSexualityId, 'label_for_app' => 'Prefer not to say', 'label_for_web' => 'Prefer not to say'],
            ['question_id' => $questionSexualityId, 'label_for_app' => 'Other', 'label_for_web' => 'Other'],

            // Employment options
            ['question_id' => $employmentQuestionId, 'label_for_app' => 'Full-time', 'label_for_web' => 'Full-time'],
            ['question_id' => $employmentQuestionId, 'label_for_app' => 'Part-time', 'label_for_web' => 'Part-time'],
            ['question_id' => $employmentQuestionId, 'label_for_app' => 'Student', 'label_for_web' => 'Student'],
            ['question_id' => $employmentQuestionId, 'label_for_app' => 'Retired', 'label_for_web' => 'Retired'],
            ['question_id' => $employmentQuestionId, 'label_for_app' => 'Shift-work', 'label_for_web' => 'Shift-work'],
            ['question_id' => $employmentQuestionId, 'label_for_app' => 'Hybrid', 'label_for_web' => 'Hybrid'],
            ['question_id' => $employmentQuestionId, 'label_for_app' => 'Backpacker', 'label_for_web' => 'Backpacker'],
            ['question_id' => $employmentQuestionId, 'label_for_app' => 'Looking for work', 'label_for_web' => 'Looking for work'],
            ['question_id' => $employmentQuestionId, 'label_for_app' => 'Graduate Program', 'label_for_web' => 'Graduate Program'],
           

            // Do you drink?
            ['question_id' => $drinkQuestionId, 'label_for_app' => 'Yes', 'label_for_web' => 'Yes'],
            ['question_id' => $drinkQuestionId, 'label_for_app' => 'Occasionally', 'label_for_web' => 'Occasionally'],
            ['question_id' => $drinkQuestionId, 'label_for_app' => 'No', 'label_for_web' => 'No'],

            // Do you smoke?
            ['question_id' => $smokeQuestionId, 'label_for_app' => 'Yes', 'label_for_web' => 'Yes'],
            ['question_id' => $smokeQuestionId, 'label_for_app' => 'Occasionally', 'label_for_web' => 'Occasionally'],
            ['question_id' => $smokeQuestionId, 'label_for_app' => 'No', 'label_for_web' => 'No'],

            // Dietary requirements (multiple choice)
            ['question_id' => $dietQuestionId, 'label_for_app' => 'Lactose Intolerance', 'label_for_web' => 'Lactose Intolerance'],
            ['question_id' => $dietQuestionId, 'label_for_app' => 'Vegetarian', 'label_for_web' => 'Vegetarian'],
            ['question_id' => $dietQuestionId, 'label_for_app' => 'Vegan', 'label_for_web' => 'Vegan'],
            ['question_id' => $dietQuestionId, 'label_for_app' => 'Pescatarian', 'label_for_web' => 'Pescatarian'],
            ['question_id' => $dietQuestionId, 'label_for_app' => 'Gluten-Free', 'label_for_web' => 'Gluten-Free'],
            ['question_id' => $dietQuestionId, 'label_for_app' => 'Allergies', 'label_for_web' => 'Allergies'],
            ['question_id' => $dietQuestionId, 'label_for_app' => 'None', 'label_for_web' => 'None'],


            // Ethnicity options for $ethnicityQuestionId = 18;
            ['question_id' => $ethnicityQuestionId, 'label_for_app' => 'Caucasian', 'label_for_web' => 'Caucasian'],
            ['question_id' => $ethnicityQuestionId, 'label_for_app' => 'Asian', 'label_for_web' => 'Asian'],
            ['question_id' => $ethnicityQuestionId, 'label_for_app' => 'African', 'label_for_web' => 'African'],
            ['question_id' => $ethnicityQuestionId, 'label_for_app' => 'Hispanic', 'label_for_web' => 'Hispanic'],
            ['question_id' => $ethnicityQuestionId, 'label_for_app' => 'Middle Eastern', 'label_for_web' => 'Middle Eastern'],
            ['question_id' => $ethnicityQuestionId, 'label_for_app' => 'Other', 'label_for_web' => 'Other'],

            // Open to team-ups
            ['question_id' => $openToTeamUps, 'label_for_app' => 'Yes', 'label_for_web' => 'Yes'],
            ['question_id' => $openToTeamUps, 'label_for_app' => 'No', 'label_for_web' => 'No'],

            // Preferred Stay Length options for $preferredStayLengthQuestionId = 20;
            ['question_id' => $preferredStayLengthQuestionId, 'label_for_app' => '2 weeks', 'label_for_web' => '2 weeks'],
            ['question_id' => $preferredStayLengthQuestionId, 'label_for_app' => '1 month', 'label_for_web' => '1 month'],
            ['question_id' => $preferredStayLengthQuestionId, 'label_for_app' => '2 months', 'label_for_web' => '2 months'],
            ['question_id' => $preferredStayLengthQuestionId, 'label_for_app' => '3 months', 'label_for_web' => '3 months'],
            ['question_id' => $preferredStayLengthQuestionId, 'label_for_app' => '6 months', 'label_for_web' => '6 months'],
            ['question_id' => $preferredStayLengthQuestionId, 'label_for_app' => '9 months', 'label_for_web' => '9 months'],
            ['question_id' => $preferredStayLengthQuestionId, 'label_for_app' => '1 year', 'label_for_web' => '1 year'],

            // Here i want to create 2 option true false for rental history
            ['question_id' => $rentalHistory, 'label_for_app' => 'Yes', 'label_for_web' => 'Yes'],
            ['question_id' => $rentalHistory, 'label_for_app' => 'No', 'label_for_web' => 'No'],

            // Property Preference options for $propertyPreferenceQuestionId = 24;
            ['question_id' => $propertyPreferenceQuestionId, 'label_for_app' => 'Flexible', 'label_for_web' => 'Flexible'],
            ['question_id' => $propertyPreferenceQuestionId, 'label_for_app' => 'Not Required', 'label_for_web' => 'Not Required'],
            ['question_id' => $propertyPreferenceQuestionId, 'label_for_app' => 'Required', 'label_for_web' => 'Required'],

            // propertyPreferenceInternetQuestionId = 82
            ['question_id' => $propertyPreferenceInternetQuestionId, 'label_for_app' => 'Flexible', 'label_for_web' => 'Flexible'],
            ['question_id' => $propertyPreferenceInternetQuestionId, 'label_for_app' => 'Required', 'label_for_web' => 'Required'],

            // propertyPreferenceBathroomQuestionId = 83
            ['question_id' => $propertyPreferenceBathroomQuestionId, 'label_for_app' => 'Flexible', 'label_for_web' => 'Flexible'],
            ['question_id' => $propertyPreferenceBathroomQuestionId, 'label_for_app' => 'Ensuite or Private', 'label_for_web' => 'Ensuite or Private'],

            // propertyPreferenceParkingQuestionId = 84
            ['question_id' => $propertyPreferenceParkingQuestionId, 'label_for_app' => 'Flexible', 'label_for_web' => 'Flexible'],
            ['question_id' => $propertyPreferenceParkingQuestionId, 'label_for_app' => 'Off-street Required', 'label_for_web' => 'Off-street Required'],

            // propertyPreferenceMaxRoommatesQuestionId = 85
            ['question_id' => $propertyPreferenceMaxRoommatesQuestionId, 'label_for_app' => 'Flexible', 'label_for_web' => 'Flexible'],
            ['question_id' => $propertyPreferenceMaxRoommatesQuestionId, 'label_for_app' => '1 Other', 'label_for_web' => '1 Other'],
            ['question_id' => $propertyPreferenceMaxRoommatesQuestionId, 'label_for_app' => '2 Other', 'label_for_web' => '2 Other'],
            
            // Political Views
            ['question_id' => $politicalViewsQuestionId, 'label_for_app' => 'Liberal', 'label_for_web' => 'Liberal'],
            ['question_id' => $politicalViewsQuestionId, 'label_for_app' => 'Moderate', 'label_for_web' => 'Moderate'],
            ['question_id' => $politicalViewsQuestionId, 'label_for_app' => 'Conservative', 'label_for_web' => 'Conservative'],
            ['question_id' => $politicalViewsQuestionId, 'label_for_app' => 'Not political', 'label_for_web' => 'Not political'],
            ['question_id' => $politicalViewsQuestionId, 'label_for_app' => 'Other', 'label_for_web' => 'Other'],

            // Open to overnight guests
            ['question_id' => $openToOvernightGuestsQuestionId, 'label_for_app' => 'Yes', 'label_for_web' => 'Yes'],
            ['question_id' => $openToOvernightGuestsQuestionId, 'label_for_app' => 'No', 'label_for_web' => 'No'],
            ['question_id' => $openToOvernightGuestsQuestionId, 'label_for_app' => 'Occasionally', 'label_for_web' => 'Occasionally'],

            // Pets
            ['question_id' => $petsQuestionId, 'label_for_app' => 'I have a pet', 'label_for_web' => 'I have a pet'],
            ['question_id' => $petsQuestionId, 'label_for_app' => 'Open to pets', 'label_for_web' => 'Open to pets'],
            ['question_id' => $petsQuestionId, 'label_for_app' => 'Not open to pets', 'label_for_web' => 'Not open to pets'],

            // Interests
            ['question_id' => $interestsQuestionId, 'label_for_app' => '☕️ Coffee', 'label_for_web' => '☕️ Coffee'],
            ['question_id' => $interestsQuestionId, 'label_for_app' => '🎾 Tennis', 'label_for_web' => '🎾 Tennis'],
            ['question_id' => $interestsQuestionId, 'label_for_app' => '🏕 Camping', 'label_for_web' => '🏕 Camping'],
            ['question_id' => $interestsQuestionId, 'label_for_app' => '🎊 Festivals', 'label_for_web' => '🎊 Festivals'],
            ['question_id' => $interestsQuestionId, 'label_for_app' => '🍜 Foodie', 'label_for_web' => '🍜 Foodie'],
            ['question_id' => $interestsQuestionId, 'label_for_app' => '🏔 Hiking', 'label_for_web' => '🏔 Hiking'],
            ['question_id' => $interestsQuestionId, 'label_for_app' => '🐶 Dogs', 'label_for_web' => '🐶 Dogs'], 
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🧘 Yoga",'label_for_web'=>"🧘 Yoga"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🏃 Running",'label_for_web'=>"🏃 Running"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🚴 Cycling",'label_for_web'=>"🚴 Cycling"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🧘‍♀️ Pilates",'label_for_web'=>"🧘‍♀️ Pilates"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🏋️ Weightlifting",'label_for_web'=>"🏋️ Weightlifting"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🏊 Swimming",'label_for_web'=>"🏊 Swimming"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🎿 Skiing",'label_for_web'=>"🎿 Skiing"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🏂 Snowboarding",'label_for_web'=>"🏂 Snowboarding"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🧗 Rock Climbing",'label_for_web'=>"🧗 Rock Climbing"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🛶 Kayaking",'label_for_web'=>"🛶 Kayaking"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🏄‍♂️ Surfing",'label_for_web'=>"🏄‍♂️ Surfing"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "⚽ Soccer",'label_for_web'=>"⚽ Soccer"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🏀 Basketball",'label_for_web'=>"🏀 Basketball"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🏐 Volleyball",'label_for_web'=>"🏐 Volleyball"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "⚾ Baseball",'label_for_web'=>"⚾ Baseball"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "⛳ Golf",'label_for_web'=>"⛳ Golf"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🥊 Boxing",'label_for_web'=>"🥊 Boxing"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🥋 Martial Arts",'label_for_web'=>"🥋 Martial Arts"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "💪 Gym",'label_for_web'=>"💪 Gym"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "💃 Dance",'label_for_web'=>"💃 Dance"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🕺 Zumba",'label_for_web'=>"🕺 Zumba"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🏋️‍♂️ CrossFit",'label_for_web'=>"🏋️‍♂️ CrossFit"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🧘‍♂️ Meditation",'label_for_web'=>"🧘‍♂️ Meditation"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🧠 Mindfulness",'label_for_web'=>"🧠 Mindfulness"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "⛸️ Skating",'label_for_web'=>"⛸️ Skating"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🛼 Rollerblading",'label_for_web'=>"🛼 Rollerblading"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🚗 Road Trips",'label_for_web'=>"🚗 Road Trips"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🎒 Backpacking",'label_for_web'=>"🎒 Backpacking"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "✈️ Traveling",'label_for_web'=>"✈️ Traveling"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🏙️ City Exploring",'label_for_web'=>"🏙️ City Exploring"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🏖️ Beaches",'label_for_web'=>"🏖️ Beaches"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "⛰️ Mountains",'label_for_web'=>"⛰️ Mountains"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🏞️ National Parks",'label_for_web'=>"🏞️ National Parks"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🤿 Scuba Diving",'label_for_web'=>"🤿 Scuba Diving"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🚤 Boating",'label_for_web'=>"🚤 Boating"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🍵 Tea",'label_for_web'=>"🍵 Tea"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🍷 Wine Tasting",'label_for_web'=>"🍷 Wine Tasting"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🍺 Beer Lover",'label_for_web'=>"🍺 Beer Lover"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🍸 Cocktails",'label_for_web'=>"🍸 Cocktails"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🧁 Baking",'label_for_web'=>"🧁 Baking"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🍳 Cooking",'label_for_web'=>"🍳 Cooking"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🌮 Street Food",'label_for_web'=>"🌮 Street Food"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🍽️ Fine Dining",'label_for_web'=>"🍽️ Fine Dining"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🧺 Farmers Markets",'label_for_web'=>"🧺 Farmers Markets"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🍥 Anime",'label_for_web'=>"🍥 Anime"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "📚 Manga",'label_for_web'=>"📚 Manga"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "📺 K-Dramas",'label_for_web'=>"📺 K-Dramas"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🎤 K-Pop",'label_for_web'=>"🎤 K-Pop"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🎶 J-Pop",'label_for_web'=>"🎶 J-Pop"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🪘 Afrobeats",'label_for_web'=>"🪘 Afrobeats"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🎧 Reggaeton",'label_for_web'=>"🎧 Reggaeton"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🎧 Hip-Hop",'label_for_web'=>"🎧 Hip-Hop"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🎸 Indie Music",'label_for_web'=>"🎸 Indie Music"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🎛️ EDM",'label_for_web'=>"🎛️ EDM"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🎻 Classical Music",'label_for_web'=>"🎻 Classical Music"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🎷 Jazz",'label_for_web'=>"🎷 Jazz"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🎙️ Podcasts",'label_for_web'=>"🎙️ Podcasts"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🎚️ DJing",'label_for_web'=>"🎚️ DJing"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🎤 Singing",'label_for_web'=>"🎤 Singing"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "📝 Songwriting",'label_for_web'=>"📝 Songwriting"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🎹 Playing Instruments",'label_for_web'=>"🎹 Playing Instruments"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🎫 Concerts",'label_for_web'=>"🎫 Concerts"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "📀 Vinyl Records",'label_for_web'=>"📀 Vinyl Records"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "📸 Photography",'label_for_web'=>"📸 Photography"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🎞️ Film",'label_for_web'=>"🎞️ Film"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🍿 Movie Buff",'label_for_web'=>"🍿 Movie Buff"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "👻 Horror Movies",'label_for_web'=>"👻 Horror Movies"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "📽️ Documentaries",'label_for_web'=>"📽️ Documentaries"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "📺 Reality TV",'label_for_web'=>"📺 Reality TV"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "📲 Streaming Binge",'label_for_web'=>"📲 Streaming Binge"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🟥 Netflix",'label_for_web'=>"🟥 Netflix"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🎵 TikTok",'label_for_web'=>"🎵 TikTok"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🎮 Twitch",'label_for_web'=>"🎮 Twitch"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🕹️ Gaming",'label_for_web'=>"🕹️ Gaming"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🎲 Board Games",'label_for_web'=>"🎲 Board Games"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🃏 Card Games",'label_for_web'=>"🃏 Card Games"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "♟️ Chess",'label_for_web'=>"♟️ Chess"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🪙 Crypto",'label_for_web'=>"🪙 Crypto"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🖼️ NFTs",'label_for_web'=>"🖼️ NFTs"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🚀 Startups",'label_for_web'=>"🚀 Startups"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "📈 Entrepreneurship",'label_for_web'=>"📈 Entrepreneurship"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "💰 Investing",'label_for_web'=>"💰 Investing"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "👨‍💻 Coding",'label_for_web'=>"👨‍💻 Coding"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🎨 Design",'label_for_web'=>"🎨 Design"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🤝 Volunteering",'label_for_web'=>"🤝 Volunteering"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "✊ Activism",'label_for_web'=>"✊ Activism"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🧠 Mental Health",'label_for_web'=>"🧠 Mental Health"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🧿 Spirituality",'label_for_web'=>"🧿 Spirituality"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🔮 Astrology",'label_for_web'=>"🔮 Astrology"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🃏 Tarot",'label_for_web'=>"🃏 Tarot"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "📓 Journaling",'label_for_web'=>"📓 Journaling"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "📝 Poetry",'label_for_web'=>"📝 Poetry"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "✍️ Creative Writing",'label_for_web'=>"✍️ Creative Writing"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "📚 Book Club",'label_for_web'=>"📚 Book Club"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🛠️ DIY",'label_for_web'=>"🛠️ DIY"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🧥 Thrifting",'label_for_web'=>"🧥 Thrifting"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🛋️ Interior Design",'label_for_web'=>"🛋️ Interior Design"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🪴 Gardening",'label_for_web'=>"🪴 Gardening"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🎨 Painting",'label_for_web'=>"🎨 Painting"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "✏️ Drawing",'label_for_web'=>"✏️ Drawing"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "✂️ Crafting",'label_for_web'=>"✂️ Crafting"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "👗 Fashion",'label_for_web'=>"👗 Fashion"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🕶️ Y2K Style",'label_for_web'=>"🕶️ Y2K Style"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "👟 Streetwear",'label_for_web'=>"👟 Streetwear"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "📼 Vintage",'label_for_web'=>"📼 Vintage"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "👟 Sneakers",'label_for_web'=>"👟 Sneakers"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "💄 Makeup",'label_for_web'=>"💄 Makeup"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🧴 Skincare",'label_for_web'=>"🧴 Skincare"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "💅 Nail Art",'label_for_web'=>"💅 Nail Art"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🛁 Self-Care",'label_for_web'=>"🛁 Self-Care"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🌙 Late Night Talks",'label_for_web'=>"🌙 Late Night Talks"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🧠 Deep Conversations",'label_for_web'=>"🧠 Deep Conversations"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🔓 Open Relationships",'label_for_web'=>"🔓 Open Relationships"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "❤️‍🔥 Polyamory",'label_for_web'=>"❤️‍🔥 Polyamory"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🏳️‍🌈 LGBTQ+ Friendly",'label_for_web'=>"🏳️‍🌈 LGBTQ+ Friendly"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "♀️ Feminism",'label_for_web'=>"♀️ Feminism"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🐶 Pet Lover",'label_for_web'=>"🐶 Pet Lover"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🐱 Cats",'label_for_web'=>"🐱 Cats"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🕊️ Bird Watching",'label_for_web'=>"🕊️ Bird Watching"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🈶 Language Learning",'label_for_web'=>"🈶 Language Learning"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🌶️ Desi Culture",'label_for_web'=>"🌶️ Desi Culture"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🥁 Bhangra",'label_for_web'=>"🥁 Bhangra"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "💃 Latin Dancing",'label_for_web'=>"💃 Latin Dancing"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🍢 Middle Eastern Food",'label_for_web'=>"🍢 Middle Eastern Food"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🍜 Asian Cuisine",'label_for_web'=>"🍜 Asian Cuisine"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🎤 Karaoke",'label_for_web'=>"🎤 Karaoke"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🧠 Trivia Nights",'label_for_web'=>"🧠 Trivia Nights"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🏛️ Museums",'label_for_web'=>"🏛️ Museums"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🖼️ Art Galleries",'label_for_web'=>"🖼️ Art Galleries"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🎭 Theatre",'label_for_web'=>"🎭 Theatre"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🎼 Musicals",'label_for_web'=>"🎼 Musicals"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🎟️ Opera",'label_for_web'=>"🎟️ Opera"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🎤 Stand-up Comedy",'label_for_web'=>"🎤 Stand-up Comedy"],
            ['question_id' => $interestsQuestionId, 'label_for_app' => "🎬 Bollywood",      'label_for_web'=>"🎬 Bollywood",     ] ,
            
            // religion
            ['question_id' => $religionQuestionId, 'label_for_app' => 'Christianity', 'label_for_web' => 'Christianity'],
            ['question_id' => $religionQuestionId, 'label_for_app' => 'Hinduism', 'label_for_web' => 'Hinduism'],
            ['question_id' => $religionQuestionId, 'label_for_app' => 'Buddhism', 'label_for_web' => 'Buddhism'],
            ['question_id' => $religionQuestionId, 'label_for_app' => 'Judaism', 'label_for_web' => 'Judaism'],
            ['question_id' => $religionQuestionId, 'label_for_app' => 'Atheist', 'label_for_web' => 'Atheist'],
            ['question_id' => $religionQuestionId, 'label_for_app' => 'Agnostic', 'label_for_web' => 'Agnostic'],

            // languagesSpeakQuestionId
            ['question_id' => $languagesSpeakQuestionId, 'label_for_app' => 'English', 'label_for_web' => 'English'],
            ['question_id' => $languagesSpeakQuestionId, 'label_for_app' => 'Spanish', 'label_for_web' => 'Spanish'],
            ['question_id' => $languagesSpeakQuestionId, 'label_for_app' => 'Mandarin', 'label_for_web' => 'Mandarin'],
            ['question_id' => $languagesSpeakQuestionId, 'label_for_app' => 'French', 'label_for_web' => 'French'],
            ['question_id' => $languagesSpeakQuestionId, 'label_for_app' => 'German', 'label_for_web' => 'German'],
            ['question_id' => $languagesSpeakQuestionId, 'label_for_app' => 'Other', 'label_for_web' => 'Other'],

            ['question_id' => $accommodationTypeQuestionId, 'label_for_app' => 'Whole property', 'label_for_web' => 'Whole property'],
            ['question_id' => $accommodationTypeQuestionId, 'label_for_app' => 'Granny Flat', 'label_for_web' => 'Granny Flat'],
            ['question_id' => $accommodationTypeQuestionId, 'label_for_app' => 'Student Accommodation', 'label_for_web' => 'Student Accommodation'],
            ['question_id' => $accommodationTypeQuestionId, 'label_for_app' => 'Room(s) in an existing sharehouse', 'label_for_web' => 'Room(s) in an existing sharehouse'],
            ['question_id' => $accommodationTypeQuestionId, 'label_for_app' => 'Room(s) in apartment', 'label_for_web' => 'Room(s) in apartment'],
            ['question_id' => $accommodationTypeQuestionId, 'label_for_app' => 'Studio', 'label_for_web' => 'Studio'],

            ['question_id' => $totalBedroomsQuestionId, 'label_for_app' => '2', 'label_for_web' => '2'],
            ['question_id' => $totalBedroomsQuestionId, 'label_for_app' => '3', 'label_for_web' => '3'],
            ['question_id' => $totalBedroomsQuestionId, 'label_for_app' => '4', 'label_for_web' => '4'],
            ['question_id' => $totalBedroomsQuestionId, 'label_for_app' => '5', 'label_for_web' => '5'],
            ['question_id' => $totalBedroomsQuestionId, 'label_for_app' => '6+', 'label_for_web' => '6+'],

            ['question_id' => $totalBathroomsQuestionId, 'label_for_app' => '2', 'label_for_web' => '2'],
            ['question_id' => $totalBathroomsQuestionId, 'label_for_app' => '3', 'label_for_web' => '3'],
            ['question_id' => $totalBathroomsQuestionId, 'label_for_app' => '4', 'label_for_web' => '4'],
            ['question_id' => $totalBathroomsQuestionId, 'label_for_app' => '5', 'label_for_web' => '5'],
            ['question_id' => $totalBathroomsQuestionId, 'label_for_app' => '6+', 'label_for_web' => '6+'],

            ['question_id' => $parkingAvailableQuestionId, 'label_for_app' => 'No parking', 'label_for_web' => 'No parking', 'image' => $optionIconPath . 'directions_car.svg'],
            ['question_id' => $parkingAvailableQuestionId, 'label_for_app' => 'Street parking', 'label_for_web' => 'Street parking', 'image' => $optionIconPath . 'directions_car.svg'],
            ['question_id' => $parkingAvailableQuestionId, 'label_for_app' => 'Private parking', 'label_for_web' => 'Private parking', 'image' => $optionIconPath . 'directions_car.svg'],

            ['question_id' => $wifiQuestionId, 'label_for_app' => 'No internet', 'label_for_web' => 'No internet', 'image' => $optionIconPath . 'wifi.svg'],
            ['question_id' => $wifiQuestionId, 'label_for_app' => 'Included in rent', 'label_for_web' => 'Included in rent', 'image' => $optionIconPath . 'wifi.svg'],
            ['question_id' => $wifiQuestionId, 'label_for_app' => 'Available but not included in rent', 'label_for_web' => 'Available but not included in rent', 'image' => $optionIconPath . 'wifi.svg'],

            ['question_id' => $homeAccessibilityQuestionId, 'label_for_app' => 'Lift', 'label_for_web' => 'Lift', 'image' => $optionIconPath . 'Lift.svg'],
            ['question_id' => $homeAccessibilityQuestionId, 'label_for_app' => 'Step-free home', 'label_for_web' => 'Step-free home'],
            ['question_id' => $homeAccessibilityQuestionId, 'label_for_app' => 'Roll-in shower', 'label_for_web' => 'Roll-in shower', 'image' => $optionIconPath . 'Roll-in_shower.svg'],
            ['question_id' => $homeAccessibilityQuestionId, 'label_for_app' => 'Bathroom grip rails', 'label_for_web' => 'Bathroom grip rails'],
            ['question_id' => $homeAccessibilityQuestionId, 'label_for_app' => 'Level access to home', 'label_for_web' => 'Level access to home'],

            ['question_id' => $propertyFacilitiesQuestionId, 'label_for_app' => 'Pool', 'label_for_web' => 'Pool', 'image' => $optionIconPath . 'Pool.svg'],
            ['question_id' => $propertyFacilitiesQuestionId, 'label_for_app' => 'Gym', 'label_for_web' => 'Gym', 'image' => $optionIconPath . 'Gym.svg'],
            ['question_id' => $propertyFacilitiesQuestionId, 'label_for_app' => 'Function Room', 'label_for_web' => 'Function Room'],
            ['question_id' => $propertyFacilitiesQuestionId, 'label_for_app' => 'Barbeque Pit', 'label_for_web' => 'Barbeque Pit', 'image' => $optionIconPath . 'Barbeque_Pit.svg'],
            ['question_id' => $propertyFacilitiesQuestionId, 'label_for_app' => 'Sauna', 'label_for_web' => 'Sauna','image' => $optionIconPath . 'Sauna.svg'],
            ['question_id' => $propertyFacilitiesQuestionId, 'label_for_app' => 'Spa', 'label_for_web' => 'Spa' , 'image' => $optionIconPath . 'Spa.svg'],

            ['question_id' => $peopleLivingQuestionId, 'label_for_app' => '2', 'label_for_web' => '2'],
            ['question_id' => $peopleLivingQuestionId, 'label_for_app' => '3', 'label_for_web' => '3'],
            ['question_id' => $peopleLivingQuestionId, 'label_for_app' => '4', 'label_for_web' => '4'],
            ['question_id' => $peopleLivingQuestionId, 'label_for_app' => '5', 'label_for_web' => '5'],
            ['question_id' => $peopleLivingQuestionId, 'label_for_app' => '6+', 'label_for_web' => '6+'],
            ['question_id' => $peopleLivingQuestionId, 'label_for_app' => 'Include me! I live in this property.', 'label_for_web' => 'Include me! I live in this property.'],

            ['question_id' => $roomTypeQuestionId, 'label_for_app' => 'Private', 'label_for_web' => 'Private'],
            ['question_id' => $roomTypeQuestionId, 'label_for_app' => 'Shared', 'label_for_web' => 'Shared'],

            ['question_id' => $roomFurnishingsQuestionId, 'label_for_app' => 'Semi-Furnished', 'label_for_web' => 'Semi-Furnished'],
            ['question_id' => $roomFurnishingsQuestionId, 'label_for_app' => 'Furnished', 'label_for_web' => 'Furnished'],
            ['question_id' => $roomFurnishingsQuestionId, 'label_for_app' => 'Unfurnished', 'label_for_web' => 'Unfurnished'],

            ['question_id' => $bathroomQuestionId, 'label_for_app' => 'Shared', 'label_for_web' => 'Shared'],
            ['question_id' => $bathroomQuestionId, 'label_for_app' => 'Own', 'label_for_web' => 'Own'],
            ['question_id' => $bathroomQuestionId, 'label_for_app' => 'Ensuite', 'label_for_web' => 'Ensuite'],

            ['question_id' => $bedSizeQuestionId, 'label_for_app' => 'Single', 'label_for_web' => 'Single'],
            ['question_id' => $bedSizeQuestionId, 'label_for_app' => 'Double', 'label_for_web' => 'Double'],
            ['question_id' => $bedSizeQuestionId, 'label_for_app' => 'Queen', 'label_for_web' => 'Queen'],
            ['question_id' => $bedSizeQuestionId, 'label_for_app' => 'King', 'label_for_web' => 'King'],
            ['question_id' => $bedSizeQuestionId, 'label_for_app' => 'None', 'label_for_web' => 'None'],

            ['question_id' => $roomFeaturesQuestionId, 'label_for_app' => 'Bedside table', 'label_for_web' => 'Bedside table'],
            ['question_id' => $roomFeaturesQuestionId, 'label_for_app' => 'Wardrobe', 'label_for_web' => 'Wardrobe', 'image' => $optionIconPath . 'door_sliding.svg'],
            ['question_id' => $roomFeaturesQuestionId, 'label_for_app' => 'Drawers', 'label_for_web' => 'Drawers'],
            ['question_id' => $roomFeaturesQuestionId, 'label_for_app' => 'Kitchenette', 'label_for_web' => 'Kitchenette'],
            ['question_id' => $roomFeaturesQuestionId, 'label_for_app' => 'Heater', 'label_for_web' => 'Heater'],
            ['question_id' => $roomFeaturesQuestionId, 'label_for_app' => 'Desk', 'label_for_web' => 'Desk'],
            ['question_id' => $roomFeaturesQuestionId, 'label_for_app' => 'Lamp', 'label_for_web' => 'Lamp'],
            ['question_id' => $roomFeaturesQuestionId, 'label_for_app' => 'Chair', 'label_for_web' => 'Chair'],
            ['question_id' => $roomFeaturesQuestionId, 'label_for_app' => 'Couch', 'label_for_web' => 'Couch'],
            ['question_id' => $roomFeaturesQuestionId, 'label_for_app' => 'TV', 'label_for_web' => 'TV'],
            ['question_id' => $roomFeaturesQuestionId, 'label_for_app' => 'Balcony', 'label_for_web' => 'Balcony'],
            ['question_id' => $roomFeaturesQuestionId, 'label_for_app' => 'Door Lock', 'label_for_web' => 'Door Lock'],
            ['question_id' => $roomFeaturesQuestionId, 'label_for_app' => 'Split Air Conditioning', 'label_for_web' => 'Split Air Conditioning', 'image' => $optionIconPath . 'aq_indoor.svg'],
            ['question_id' => $roomFeaturesQuestionId, 'label_for_app' => 'Ducted Air Conditioning', 'label_for_web' => 'Ducted Air Conditioning', 'image' => $optionIconPath . 'aq_indoor.svg'],

            ['question_id' => $billIncludedQuestionId, 'label_for_app' => 'Yes', 'label_for_web' => 'Yes'],
            ['question_id' => $billIncludedQuestionId, 'label_for_app' => 'No', 'label_for_web' => 'No'],

            ['question_id' => $minStayQuestionId, 'label_for_app' => '2 weeks', 'label_for_web' => '2 weeks'],
            ['question_id' => $minStayQuestionId, 'label_for_app' => '1 month', 'label_for_web' => '1 month'],
            ['question_id' => $minStayQuestionId, 'label_for_app' => '2 months', 'label_for_web' => '2 months'],
            ['question_id' => $minStayQuestionId, 'label_for_app' => '3 months', 'label_for_web' => '3 months'],
            ['question_id' => $minStayQuestionId, 'label_for_app' => '6 months', 'label_for_web' => '6 months'],
            ['question_id' => $minStayQuestionId, 'label_for_app' => '9 months', 'label_for_web' => '9 months'],
            ['question_id' => $minStayQuestionId, 'label_for_app' => '1 year', 'label_for_web' => '1 year'],
            ['question_id' => $minStayQuestionId, 'label_for_app' => 'Flexible', 'label_for_web' => 'Flexible'],

            ['question_id' => $maxStayQuestionId, 'label_for_app' => '2 weeks', 'label_for_web' => '2 weeks'],
            ['question_id' => $maxStayQuestionId, 'label_for_app' => '1 month', 'label_for_web' => '1 month'],
            ['question_id' => $maxStayQuestionId, 'label_for_app' => '2 months', 'label_for_web' => '2 months'],
            ['question_id' => $maxStayQuestionId, 'label_for_app' => '3 months', 'label_for_web' => '3 months'],
            ['question_id' => $maxStayQuestionId, 'label_for_app' => '6 months', 'label_for_web' => '6 months'],
            ['question_id' => $maxStayQuestionId, 'label_for_app' => '9 months', 'label_for_web' => '9 months'],
            ['question_id' => $maxStayQuestionId, 'label_for_app' => '1 year', 'label_for_web' => '1 year'],
            ['question_id' => $maxStayQuestionId, 'label_for_app' => 'Flexible', 'label_for_web' => 'Flexible'],

            ['question_id' => $housematePrefQuestionId, 'label_for_app' => 'Anyone', 'label_for_web' => 'Anyone'],
            ['question_id' => $housematePrefQuestionId, 'label_for_app' => 'Woman', 'label_for_web' => 'Woman'],
            ['question_id' => $housematePrefQuestionId, 'label_for_app' => 'Man', 'label_for_web' => 'Man'],
            ['question_id' => $housematePrefQuestionId, 'label_for_app' => 'No couples', 'label_for_web' => 'No couples'],
            ['question_id' => $housematePrefQuestionId, 'label_for_app' => 'Couples only', 'label_for_web' => 'Couples only'],

            ['question_id' => $acceptingOfQuestionId, 'label_for_app' => 'Students', 'label_for_web' => 'Students'],
            ['question_id' => $acceptingOfQuestionId, 'label_for_app' => 'LGBTQIA+', 'label_for_web' => 'LGBTQIA+'],
            ['question_id' => $acceptingOfQuestionId, 'label_for_app' => 'Smokers', 'label_for_web' => 'Smokers'],
            ['question_id' => $acceptingOfQuestionId, 'label_for_app' => 'Retired', 'label_for_web' => 'Retired'],
            ['question_id' => $acceptingOfQuestionId, 'label_for_app' => 'Backpackers', 'label_for_web' => 'Backpackers'],
            ['question_id' => $acceptingOfQuestionId, 'label_for_app' => 'Unemployed/Welfare', 'label_for_web' => 'Unemployed/Welfare'],


            // house mates questions series from
            ['question_id' => $whatGenderBestDescribesYou, 'label_for_app' => 'Women', 'label_for_web' => 'Women'],
            ['question_id' => $whatGenderBestDescribesYou, 'label_for_app' => 'Man', 'label_for_web' => 'Man'],
            ['question_id' => $whatGenderBestDescribesYou, 'label_for_app' => 'Non-binary', 'label_for_web' => 'Non-binary'],
            ['question_id' => $whatGenderBestDescribesYou, 'label_for_app' => 'Trans-Women', 'label_for_web' => 'Trans-Women'],
            ['question_id' => $whatGenderBestDescribesYou, 'label_for_app' => 'Trans-Man', 'label_for_web' => 'Trans-Man'],


            ['question_id' => $ethnicity, 'label_for_app' => 'African/African American/Black', 'label_for_web' => 'African/African American/Black'],
            ['question_id' => $ethnicity, 'label_for_app' => 'South Asian', 'label_for_web' => 'South Asian'],
            ['question_id' => $ethnicity, 'label_for_app' => 'East Asian', 'label_for_web' => 'East Asian'],
            ['question_id' => $ethnicity, 'label_for_app' => 'Southeast Asian', 'label_for_web' => 'Southeast Asian'],
            ['question_id' => $ethnicity, 'label_for_app' => 'Hispanic/Latino', 'label_for_web' => 'Hispanic/Latino'],
            ['question_id' => $ethnicity, 'label_for_app' => 'Middle Eastern/North African', 'label_for_web' => 'Middle Eastern/North African'],
            ['question_id' => $ethnicity, 'label_for_app' => 'Others', 'label_for_web' => 'Others'],
            ['question_id' => $ethnicity, 'label_for_app' => 'Native American/Indigenous', 'label_for_web' => 'Native American/Indigenous'],
            ['question_id' => $ethnicity, 'label_for_app' => 'Pacific Islander', 'label_for_web' => 'Pacific Islander'],
            ['question_id' => $ethnicity, 'label_for_app' => 'White/Caucasian', 'label_for_web' => 'White/Caucasian'],
            ['question_id' => $ethnicity, 'label_for_app' => 'Multiracial/Mixed', 'label_for_web' => 'Multiracial/Mixed'],
            ['question_id' => $ethnicity, 'label_for_app' => 'Prefer not to say', 'label_for_web' => 'Prefer not to say'],
            
        ];

        foreach ($data as &$opt) {
            $opt['instruction'] = null;
            $opt['value'] = null;
            $opt['min_val'] = null;
            $opt['max_val'] = null;
            $opt['created_at'] = now();
            $opt['updated_at'] = now();
        }
        
        foreach($data as $newData){
            QuestionsOption::UpdateOrCreate([
                'label_for_app' => $newData['label_for_app'],
            ],
            $newData);
        }
    }
}
 