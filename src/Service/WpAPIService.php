<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\Celebrity;
use App\Entity\Company;
use App\Entity\Email;
use App\Entity\Link;
use App\Entity\Location;
use App\Entity\Phone;
use App\Entity\Representative;
use App\Entity\RepresentativeConnection;
use App\Entity\RepresentativeType;
use App\Entity\Settings;
use App\Entity\Status;
use Doctrine\Common\Persistence\ManagerRegistry;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class WpAPIService
{

    protected $client;
    protected $auth    = [];
    protected $headers = [
        'Content-Type' => 'application/json'
    ];

    protected $registry;
    protected $agent_mapping = [
        'publicists'     => 'publicist',
        'managers'       => 'manager',
        'booking_agents' => 'agent'
    ];

    protected $social_mapping = [
        'person_social_facebook_url'    => Link::FACEBOOK,
        'person_social_google_plus_url' => Link::GOOGLEPLUS,
        'person_social_twitter_url'     => Link::TWITTER,
        'person_social_instagram_url'   => Link::INSTAGRAM,
        'person_social_youtube_url'     => Link::YOUTUBE
    ];

    //    protected $key;

    /**
     * WpAPIService constructor.
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @return mixed
     */
    public function getClient()
    {
        if (is_null($this->client)) {
            $this->initClient();
        }

        return $this->client;
    }


    protected function initClient()
    {

        $settings = $this->registry->getRepository(Settings::class);

        $user = $settings->findOneBy(['name' => 'api_username']);
        $pass = $settings->findOneBy(['name' => 'api_password']);
        $type = $settings->findOneBy(['name' => 'api_auth']);
        $base_uri = $settings->findOneBy(['name' => 'api_url']);

        if (is_null($user)) {
            throw new \Exception('API User not set');
        }
        if (is_null($pass)) {
            throw new \Exception('API password not set');
        }
        if (is_null($type)) {
            throw new \Exception('API authorization type not set');
        }
        if (is_null($base_uri)) {
            throw new \Exception('API URL not set');
        }

        $this->auth = [$user->getValue(), $pass->getValue()];

        if ($type->getValue() === 'token') {
            $client = new Client([
                'base_uri' => $base_uri->getValue(),
                'timeout'  => 60.0,
                'verify'   => false,
                'headers'  => [
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ]
            ]);

            $response = $client->request('POST', 'jwt-auth/v1/token', [
                'form_params' => [
                    'username' => $user->getValue(),
                    'password' => $pass->getValue()
                ]
            ]);
            $data = json_decode($response->getBody(), true);
            if (isset($data['token'])) {
                $this->headers['Authorization'] = 'Bearer ' . $data['token'];
            }
            $this->auth = null;
        }
        $this->client = new Client([
            'base_uri' => $base_uri->getValue(),
            'timeout'  => 60.0,
            'verify'   => false,
            'auth'     => $this->auth,
            'headers'  => $this->headers
        ]);
    }

    public function saveCelebrity(Celebrity $celebrity)
    {
        error_log("starting saving celebrity");
        //if status set as DELETED - then delete Rep from WP
        if ($celebrity->getStatus() == Status::DELETED) {
            $this->deleteCelebrity($celebrity);

            return $celebrity->getWpId();
        }
        $payload = [
            'title'      => $celebrity->getName(),
            'content'    => $celebrity->getBio(),
            'acf_fields' => [
                'person_profession'      => $celebrity->getProfession(),
                'person_born_in_city'    => $celebrity->getCity(),
                'person_born_in_state'   => $celebrity->getState(),
                'person_born_in_country' => $celebrity->getCountry(),
                'video_youtube_video_id' => $celebrity->getYoutube(),
                'booking_price'          => $celebrity->getPrice(),
                'email_or_phone_number'  => $celebrity->getDirectAddress(),
                'deceased_celebrity'     => $celebrity->getDeceased(),
                'celebrity_hiatus'       => $celebrity->getHiatus(),
                'instagram'              => $celebrity->getInstagram(),
                'self_managed'           => $celebrity->getSelfManaged()
            ]
        ];

        foreach ($celebrity->getLinks() as $link) {
            $payload['acf_fields']['person_social_' . $link->getType() . "_url"] = $link->getUrl();
        }

        foreach ($celebrity->getCategory() as $category) {
            $payload['celebrity_category'][] = $category->getWPName();
        }

        if ($celebrity->getPrimaryCategory()) {
            $payload['primary_celebrity_category'] = $celebrity->getPrimaryCategory()->getWPName();
        }
        if ($celebrity->getBirthdate()) {
            $payload['acf_fields']['person_birthday'] = $celebrity->getBirthdate()->format("m/d/Y");
        }

        $payload['acf_fields']['booking_agents'] = [];
        $payload['acf_fields']['managers'] = [];
        $payload['acf_fields']['publicists'] = [];
        foreach ($celebrity->getRepresentativeConnections() as $representativeConnection) {
            $field = false;
            switch ($representativeConnection->getType()) {
                case 'agent':
                    $field = 'booking_agents';
                    break;
                case 'manager':
                    $field = 'managers';
                    break;
                case 'publicist':
                    $field = 'publicists';
                    break;
            }
            if ($field && !is_null($connectionWpID = $representativeConnection->getConnectedWpId())) {
                $payload['acf_fields'][$field][] = [
                    'representative' => $connectionWpID,
                    'territory'      => $representativeConnection->getTerritory()
                ];
            }
        }

        if ($celebrity->getStatus() == Status::LIVE) {
            $payload['status'] = 'publish';
        } else {
            $payload['status'] = 'draft';
        }

        $url = "wp/v2/celebrities";

        if ($celebrity->getWpId()) {
            $url .= "/" . $celebrity->getWpId();
        }

        error_log("celebrity data collected");
        error_log(json_encode($payload, JSON_UNESCAPED_UNICODE));
        $result = $this->doPost($url, $payload);
        error_log("celebrity data sent");
        return $result;
    }


    public function saveCompany(Company $company)
    {
        error_log("starting saving company");
        //if status set as DELETED - then delete Rep from WP
        if ($company->getStatus() == Status::DELETED) {
            $this->deleteCompany($company);

            return $company->getWpId();
        }
        $payload = [
            'title'      => $company->getName(),
            'content'    => $company->getDescription(),
            'acf_fields' => [
                'company_details' => [],
                'company_instagram' => $company->getInstagram(),
                'company_website' => $company->getWebsite()
            ]
        ];

        foreach ($company->getLocations() as $location) {
            $payload['acf_fields']['company_details'][] = [
                'location_name'           => $location->getName(),
                'address_company'         => ($location->getPostalAddress()),
                'visitor_address_company' => ($location->getVisitorAddress()),
                'phone_company'           => $location->getPhone(),
                'email_company'           => $location->getEmail()
            ];
        }

        foreach ($company->getCategories() as $category) {
            $payload['celebrity_category'][] = $category->getWPName();
        }

        if ($company->getPrimaryCategory()) {
            $payload['primary_celebrity_category'] = $company->getPrimaryCategory()->getWPName();
        }

        if ($company->getStatus() == Status::LIVE) {
            $payload['status'] = 'publish';
        } else {
            $payload['status'] = 'draft';
        }

        $url = "wp/v2/companies";

        if ($company->getWpId()) {
            $url .= "/" . $company->getWpId();
        }

        error_log(json_encode($payload, JSON_UNESCAPED_UNICODE));
        $result = $this->doPost($url, $payload);

        return $result;
    }


    public function saveRepresentative(Representative $representative)
    {

        //if status set as DELETED - then delete Rep from WP
        if ($representative->getStatus() == Status::DELETED) {
            $this->deleteRepresentative($representative);

            return $representative->getWpId();
        }

        $types = [];
        foreach ($representative->getType() as $type) {
            $types[] = $type->getApiName();
        }
        $companies = [];
        foreach ($representative->getCompanies() as $company) {
            $companies[] = $company->getWpId();
        }
        $payload = [
            'title'               => $representative->getName(),
            'representative_type' => $types,
            'acf_fields'          => [
                'contact_info' => [
                    [
                        'company_name'         => $representative->getCompanyName(),
                        'company_cpt'         =>  $companies,
                        'address_for_visitors' => $representative->getVisitorAddress(),
                        'address'              => $representative->getMailingAddress(),
                        'phone_numbers'        => [],
                        'email_addresses'      => []
                    ]
                ],
                'instagram' => $representative->getInstagram()
            ]
        ];

        if ($representative->getPrimaryCategory()) {
            $payload['primary_celebrity_category'] = $representative->getPrimaryCategory()->getWPName();
        }

        foreach ($representative->getPhones() as $phone) {
            if ($phone->getDeleted()) {
                continue;
            }
            $payload['acf_fields']['contact_info'][0]['phone_numbers'][] = [
                'display_number' => $phone->getPhone(),
                'real_number'    => ''
            ];
        }
        foreach ($representative->getEmails() as $email) {
            if ($email->getDeleted()) {
                continue;
            }
            $payload['acf_fields']['contact_info'][0]['email_addresses'][] = [
                'email_address' => $email->getEmail()
            ];
        }
        foreach ($representative->getCategories() as $category) {
            $payload['celebrity_category'][] = $category->getWPName();
        }

        if ($representative->getStatus() == Status::LIVE) {
            $payload['status'] = 'publish';
        } else {
            $payload['status'] = 'draft';
        }

        $url = "wp/v2/representatives";

        if ($representative->getWpId()) {
            $url .= "/" . $representative->getWpId();
        }

        return $this->doPost($url, $payload);
    }

    public function deleteCelebrity(Celebrity $celebrity)
    {

        if ($celebrity->getWpId()) {
            $url = "wp/v2/celebrities/" . $celebrity->getWpId();

            try {
                $response = $this->getClient()->request('DELETE', $url);

                $result = json_decode($response->getBody(), true);

                return $result['id'];
            } catch (GuzzleException $e) {
                return null;
            }
        }
    }

    public function deleteCompany(Company $company)
    {

        if ($company->getWpId()) {
            $url = "wp/v2/companies/" . $company->getWpId();

            try {
                $response = $this->getClient()->request('DELETE', $url);

                $result = json_decode($response->getBody(), true);

                return $result['id'];
            } catch (GuzzleException $e) {
                return null;
            }
        }
    }

    public function deleteRepresentative(Representative $representative)
    {

        if ($representative->getWpId()) {
            $url = "wp/v2/representatives/" . $representative->getWpId();

            try {
                $response = $this->getClient()->request('DELETE', $url);

                $result = json_decode($response->getBody(), true);

                return $result['id'];
            } catch (GuzzleException $e) {
                return null;
            }
        }
    }

    protected function doPost($url, $payload)
    {

        try {
            $response = $this->getClient()->request('POST', $url, [
                'body' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            ]);

            file_put_contents(__DIR__ . "/../../post.json", json_encode($payload, JSON_UNESCAPED_UNICODE));
            $result = json_decode($response->getBody(), true);

            if (null === $result) {
                error_log("Invalid JSON response from API:" . $response->getBody());
                throw new \Exception("Invalid JSON response from API");
            }
            return $result['id'];
        } catch (GuzzleException $e) {
            if ($e instanceof ClientException) {
                header('x-payload: ' . $e->getRequest()->getBody()->getContents());
            }
            throw $e;
        }
    }

    public function importCelebrity(Celebrity $celebrity)
    {
        if ($celebrity->getWpId()) {
            $url = "wp/v2/celebrities/" . $celebrity->getWpId();

            try {
                $response = $this->getClient()->request('GET', $url);
                $result = json_decode($response->getBody(), true);

                return $this->importCelebrityData($result, $celebrity);
            } catch (GuzzleException $e) {
                if ($e instanceof RequestException && !is_null($e->getResponse()) && $e->getResponse()->getStatusCode() == 404) {
                    if (Status::DELETED != $celebrity->getStatus()) {
                        //mark representative as deleted
                        //mark celebrity as deleted
                        $celebrity->setValidTill(new \DateTime());
                    }


                    return $celebrity;
                }
                throw $e;
            }
        }

        return false;
    }

    public function importCelebrityData($entry, Celebrity $celebrity)
    {

        if ('publish' == $entry['post_status']) {
            $celebrity->setStatus(Status::LIVE);
        } elseif ('trash' == $entry['post_status']) {
            //if removed - mark as removed and immediately return
            $celebrity->setValidTill(new \DateTime());

            return $celebrity;
        } else {
            $celebrity->setStatus(Status::DRAFT);
        }

        $categoryRepository = $this->registry->getRepository(Category::class);
        $representativeRepository = $this->registry->getRepository(Representative::class);
        $companyRepository = $this->registry->getRepository(Company::class);
        $rcrepository = $this->registry->getRepository(RepresentativeConnection::class);

        $manager = $this->registry->getManager();

        $celebrity->setName(html_entity_decode($entry['title']['rendered']))
            ->setWpId($entry['id'])
            ->setBio($entry['content']['rendered']);

        foreach ($entry['celebrity_category'] as $cat_name) {
            $cat_name = html_entity_decode($cat_name);
            $category = $categoryRepository->findOneBy(['name' => $cat_name]);
            if (!$category) {
                $category = new Category();
                $category->setName($cat_name);
                $manager->persist($category);
            }
            $celebrity->addCategory($category);
        }

        if (!empty($entry['primary_celebrity_category'])) {
            $category = $categoryRepository->findOneBy(['name' => $entry['primary_celebrity_category']]);
            if (!$category) {
                $category = new Category();
                $category->setName($entry['primary_celebrity_category']);
                $manager->persist($category);
            }
            $celebrity->setPrimaryCategory($category);
        }
        if (!empty($entry['featured_media'])) {
            try {
                $image = $this->getMediaData($entry['featured_media']);
                $celebrity->setImage($image['source_url']);
            } catch (GuzzleException $e) {
                //silently skip setting image
            }
        }

        if (is_array($entry['acf'])) {
            if (!empty($entry['acf']['person_birthday'])) {
                $celebrity->setBirthdate(new \DateTime($entry['acf']['person_birthday']));
            }
            if (!empty($entry['acf']['person_born_in_city'])) {
                $celebrity->setCity($entry['acf']['person_born_in_city']);
            }
            if (!empty($entry['acf']['person_born_in_state'])) {
                $celebrity->setState($entry['acf']['person_born_in_state']);
            }
            if (!empty($entry['acf']['person_born_in_country'])) {
                $celebrity->setCountry($entry['acf']['person_born_in_country']);
            }
            if (!empty($entry['acf']['person_profession']) && is_array($entry['acf']['person_profession'])) {
                $celebrity->setProfession($entry['acf']['person_profession'][0]);
            }
            if (!empty($entry['acf']['video_youtube_video_id'])) {
                $celebrity->setYoutube($entry['acf']['video_youtube_video_id']);
            }
            if (!empty($entry['acf']['booking_price'])) {
                $celebrity->setPrice($entry['acf']['booking_price']);
            }
            if (isset($entry['acf']['deceased_celebrity'])) {
                $celebrity->setDeceased($entry['acf']['deceased_celebrity']);
            }
            if (isset($entry['acf']['celebrity_hiatus'])) {
                $celebrity->setHiatus($entry['acf']['celebrity_hiatus']);
            }
            if (isset($entry['acf']['instagram'])) {
                $celebrity->setInstagram($entry['acf']['instagram']);
            }
            if (isset($entry['acf']['self_managed'])) {
                $celebrity->setSelfManaged($entry['acf']['self_managed']);
            }
        }

        $celebrity->removeLinks();
        foreach ($this->social_mapping as $field => $link_type) {
            if (!empty($entry['acf'][$field])) {
                $link = new Link();
                $link->setUrl($entry['acf'][$field]);
                $link->setType($link_type);
                $link->setCelebrity($celebrity);
                $link->setText('');
                $link->setDeleted(0);
                $manager->persist($link);
            }
        }

        $rcs = $celebrity->getRepresentativeConnections();
        foreach ($rcs as $rc) {
            $celebrity->removeRepresentativeConnection($rc);
        }
        $counter = 0;
        foreach ($this->agent_mapping as $field => $connection_type) {
            if (isset($entry['acf'][$field]) && is_array($entry['acf'][$field])) {

                foreach ($entry['acf'][$field] as $rep) {
                    if (!is_array($rep['representative'])) {
                        continue;
                    }
                    $counter++;
                    if ('company' == $rep['representative']['post_type']) {
                        $company = $companyRepository->findOneBy(['wp_id' => $rep['representative']['ID']]);
                        if (!$company) {
                            $company = new Company();
                            $company->setWpId($rep['representative']['ID']);
                            $company->setUser($celebrity->getUser());
                            $company->setCreated(new \DateTime());
                            $company = $this->importCompany($company);

                            $manager->persist($company);
                            $connection = new RepresentativeConnection();
                        } else {
                            $connection = $rcrepository->findOneBy(
                                [
                                    'celebrity' => $celebrity,
                                    'company'   => $company
                                ]
                            );

                            if (!$connection) {
                                $connection = new RepresentativeConnection();
                            }
                        }

                        $connection->setCelebrity($celebrity);
                        $connection->setCompany($company);
                        $connection->setIsCompany(true);
                        $connection->setTerritory($rep['territory']);
                        $connection->setType($connection_type);
                        $connection->setPosition($counter);
                        $celebrity->addRepresentativeConnection($connection);
                        $manager->persist($connection);
                    } else {
                        $agent = $representativeRepository->findOneBy(['wp_id' => $rep['representative']['ID']]);
                        if (!$agent) {
                            $agent = new Representative();
                            $agent->setWpId($rep['representative']['ID']);
                            $agent->setUser($celebrity->getUser());
                            $agent->setCreated(new \DateTime());
                            $agent = $this->importRepresentative($agent);

                            $manager->persist($agent);
                            $connection = new RepresentativeConnection();
                        } else {
                            $connection = $rcrepository->findOneBy(
                                [
                                    'celebrity'      => $celebrity,
                                    'representative' => $agent
                                ]
                            );

                            if (!$connection) {
                                $connection = new RepresentativeConnection();
                            }
                        }

                        $connection->setCelebrity($celebrity);
                        $connection->setRepresentative($agent);
                        $connection->setTerritory($rep['territory']);
                        $connection->setType($connection_type);
                        $connection->setPosition($counter);
                        $celebrity->addRepresentativeConnection($connection);
                        $manager->persist($connection);
                    }
                }
            }
        }

        if (is_null($celebrity->getValidFrom())) {
            $celebrity->setValidFrom(new \DateTime());
        }
        if (is_null($celebrity->getCreated())) {
            $celebrity->setCreated(new \DateTime());
        }
        if ('publish' == $entry['post_status']) {
            $celebrity->setStatus(Status::LIVE);
        } elseif ('trash' == $entry['post_status']) {
            $celebrity->setValidTill(new \DateTime());
        } else {
            $celebrity->setStatus(Status::DRAFT);
        }

        $manager->persist($celebrity);

        return $celebrity;
    }

    public function importRepresentative(Representative $representative)
    {

        if ($representative->getWpId()) {
            $url = "wp/v2/representatives/" . $representative->getWpId();

            try {
                $response = $this->getClient()->request('GET', $url);

                $result = json_decode($response->getBody(), true);

                return $this->importRepresentativeData($result, $representative);
            } catch (GuzzleException $e) {
                if ($e instanceof RequestException && $e->getResponse()->getStatusCode() == 404) {
                    if (Status::DELETED != $representative->getStatus()) {
                        //mark representative as deleted
                        $representative->setValidTill(new \DateTime());
                    }

                    return $representative;
                }
                throw $e;
            }
        }

        return false;
    }

    public function importCompany(Company $company)
    {

        if ($company->getWpId()) {
            $url = "wp/v2/companies/" . $company->getWpId();

            try {
                $response = $this->getClient()->request('GET', $url);

                $result = json_decode($response->getBody(), true);

                return $this->importCompanyData($result, $company);
            } catch (GuzzleException $e) {
                if ($e instanceof RequestException && $e->getResponse()->getStatusCode() == 404) {
                    return $company;
                }
                throw $e;
            }
        }

        return false;
    }

    public function importCompanyData($entry, Company $company)
    {
        if ('publish' == $entry['post_status']) {
            $company->setStatus(Status::LIVE);
        } elseif ('trash' == $entry['post_status']) {

            return $company;
        } else {
            $company->setStatus(Status::DRAFT);
        }

        $categoryRepository = $this->registry->getRepository(Category::class);
        $lrepository = $this->registry->getRepository(Location::class);

        $manager = $this->registry->getManager();

        $company->setName(html_entity_decode($entry['title']['rendered']))
            ->setWpId($entry['id'])
            ->setDescription($entry['content']['rendered']);

        foreach ($entry['celebrity_category'] as $cat_name) {
            $cat_name = html_entity_decode($cat_name);
            $category = $categoryRepository->findOneBy(['name' => $cat_name]);
            if (!$category) {
                $category = new Category();
                $category->setName($cat_name);
                $manager->persist($category);
            }
            $company->addCategory($category);
        }

        if (!empty($entry['primary_celebrity_category'])) {
            $category = $categoryRepository->findOneBy(['name' => $entry['primary_celebrity_category']]);
            if (!$category) {
                $category = new Category();
                $category->setName($entry['primary_celebrity_category']);
                $manager->persist($category);
            }
            $company->setPrimaryCategory($category);
        }

        if (is_array($entry['acf'])) {
            if (!empty($entry['acf']['company_instagram'])) {
                $company->setInstagram($entry['acf']['company_instagram']);
            }
            if (!empty($entry['acf']['company_website'])) {
                $company->setWebsite($entry['acf']['company_website']);
            }
            if (!empty($entry['acf']['company_details'])) {
                foreach ($entry['acf']['company_details'] as $entry) {
                    if (empty($entry)) {
                        //don't add empty locations
                        continue;
                    }

                    $location = $lrepository->findOneBy(['name' => $entry['location_name']]);
                    if (is_null($location)) {
                        $location = new Location();
                    }
                    $location->setName($entry['location_name']);
                    $location->setVisitorAddress($entry['visitor_address_company']);
                    $location->setPostalAddress($entry['address_company']);
                    $location->setEmail($entry['email_company']);
                    $location->setPhone($entry['phone_company']);
                    $manager->persist($location);
                    $company->addLocation($location);
                }
            }
        }

        return $company;
    }

    public function importRepresentativeData($entry, Representative $agent)
    {

        if ('publish' == $entry['post_status']) {
            $agent->setStatus(Status::LIVE);
        } elseif ('trash' == $entry['post_status']) {
            //agent is deleted, so mark it deleted and return immediately
            $agent->setValidTill(new \DateTime());

            return $agent;
        } else {
            $agent->setStatus(Status::DRAFT);
        }

        $manager = $this->registry->getManager();
        $categoryRepository = $this->registry->getRepository(Category::class);
        $companyRepository = $this->registry->getRepository(Company::class);
        $trepository = $this->registry->getRepository(RepresentativeType::class);

        $agent->setName(html_entity_decode($entry['title']['rendered']))
            ->setWpId($entry['id']);

        foreach ($entry['celebrity_category'] as $cat_name) {
            $cat_name = html_entity_decode($cat_name);
            $category = $categoryRepository->findOneBy(['name' => $cat_name]);
            if (!$category) {
                $category = new Category();
                $category->setName($cat_name);
                $manager->persist($category);
            }
            $agent->addCategory($category);
        }
        if (!empty($entry['primary_celebrity_category'])) {
            $category = $categoryRepository->findOneBy(['name' => $entry['primary_celebrity_category']]);
            if (!$category) {
                $category = new Category();
                $category->setName($entry['primary_celebrity_category']);
                $manager->persist($category);
            }
            $agent->setPrimaryCategory($category);
        }
        if (!empty($entry['featured_media'])) {
            try {
                $image = $this->getMediaData($entry['featured_media']);
                $agent->setImage($image['source_url']);
            } catch (GuzzleException $e) {
                //silently skip setting image
            }
        }

        if (is_array($entry['acf']['contact_info']) && is_array($entry['acf']['contact_info'][0])) {
            $agent->setLocation(null)
                ->setVisitorAddress(strip_tags($entry['acf']['contact_info'][0]['address_for_visitors']))
                ->setMailingAddress(strip_tags($entry['acf']['contact_info'][0]['address']));
            if (is_array($entry['acf']['contact_info'][0]['company_cpt'])) {
                $agent->clearCompanies();
                foreach ($entry['acf']['contact_info'][0]['company_cpt'] as $companyData) {
                    $company = $companyRepository->findOneBy(['wp_id' => $companyData['ID']]);
                    if (is_null($company)) {
                        $company = new Company();
                        $company->setWpId($companyData['ID']);
                        $company->setUser($agent->getUser());
                        $company->setCreated(new \DateTime());
                        $company = $this->importCompany($company);

                        $manager->persist($company);
                    }
                    $agent->addCompany($company);
                }
            }
            if (is_array($entry['acf']['contact_info'][0]['phone_numbers'])) {
                $agent->removePhones();
                foreach ($entry['acf']['contact_info'][0]['phone_numbers'] as $ph) {
                    $phone = new Phone();
                    $phone->setPhone($ph['display_number']);
                    $agent->addPhone($phone);
                    $manager->persist($phone);
                }
            }
            if (is_array($entry['acf']['contact_info'][0]['email_addresses'])) {
                $agent->removeEmails();
                foreach ($entry['acf']['contact_info'][0]['email_addresses'] as $em) {
                    $email = new Email();
                    $email->setEmail($em['email_address']);
                    $agent->addEmail($email);
                    $manager->persist($email);
                }
            }
        }

        foreach ($entry['representative_type'] as $type) {
            $t = false;
            switch ($type) {
                case "Agents":
                    $t = $trepository->findOneBy(['name' => 'agent']);
                    if (!$t) {
                        $t = new RepresentativeType('agent');
                    }
                    break;
                case "Managers":
                    $t = $trepository->findOneBy(['name' => 'manager']);
                    if (!$t) {
                        $t = new RepresentativeType('manager');
                    }
                    break;
                case "Publicists":
                    $t = $trepository->findOneBy(['name' => 'publicist']);
                    if (!$t) {
                        $t = new RepresentativeType('publicist');
                    }
                    break;
            }

            if ($t) {
                $agent->addType($t);
                $manager->persist($t);
            }
        }

        if (isset($entry['acf']['instagram'])) {
            $agent->setInstagram($entry['acf']['instagram']);
        }

        $agent->setValidFrom(new \DateTime());
        if (is_null($agent->getCreated())) {
            $agent->setCreated(new \DateTime());
        }

        $manager->persist($agent);

        return $agent;
    }

    public function get($url, $query)
    {
        return $this->getClient()->request('GET', $url, [
            'query' => $query
        ]);
    }

    /**
     * Uploads file to WP
     * @param UploadedFile $file
     * @return mixed
     * @throws GuzzleException
     * @see https://gist.github.com/s-hiroshi/3477e07454d809b9d38f
     */
    public function uploadFile(UploadedFile $file)
    {

        /*
         * Get binary data of image.
         * $path is file path to be uploaded.
         */
        $path = $file->getRealPath();
        $handle = fopen($path, 'r');
        $fdata = fread($handle, filesize($path));
        /*
         * Post media.
         * Request to WP REST API media endpoint
         */
        $response = $this->getClient()->request(
            'POST',
            'wp/v2/media',
            [
                'multipart' => [
                    [
                        'name'     => 'file',
                        'contents' => $fdata,
                        'filename' => basename($file->getClientOriginalName()),
                    ],

                ],
            ]
        );

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * returns information about media object
     * @param $id
     * @return mixed
     * @throws GuzzleException
     */
    public function getMediaData($id)
    {
        try {
            $response = $this->getClient()->request('GET', 'wp/v2/media/' . $id);

            $result = json_decode($response->getBody(), true);

            return $result;
        } catch (GuzzleException $e) {
            throw $e;
        }
    }

    public function setFeaturedImage($entity, $attachment_id)
    {
        $payload = [
            'featured_media' => $attachment_id
        ];

        if ($entity instanceof Celebrity) {
            $url = 'wp/v2/celebrities/' . $entity->getWpId();
        } elseif ($entity instanceof Representative) {
            $url = 'wp/v2/representatives/' . $entity->getWpId();
        } elseif ($entity instanceof Company) {
            $url = 'wp/v2/companies/' . $entity->getWpId();
        } else {
            throw new \Exception(sprinf("Unsupported entity type for setting featured image: %s", get_class($entity)));
        }
        try {
            //update alt and caption of image
            $this->getClient()->post(
                'wp/v2/media/' . $attachment_id,
                [
                    'form_params' => [
                        'alt_text' => $entity->getImageAlt(),
                        'caption'  => $entity->getImageTitle(),
                    ],
                ]
            );

            $response = $this->getClient()->request('POST', $url, [
                'body' => json_encode($payload),
            ]);

            $result = json_decode($response->getBody(), true);

            return $result;
        } catch (GuzzleException $e) {
            return false;
        }
    }

    public function getCategories()
    {
        try {
            $response = $this->getClient()->request('GET', 'wp/v2/celebrity_category?per_page=100');

            $result = json_decode($response->getBody(), true);

            return $result;
        } catch (GuzzleException $e) {
            throw $e;
        }
    }

    public function getAttachmentIdByUrl($url)
    {
        try {
            $response = $this->getClient()->request('GET', 'wp/v2/get_attachment_id', [
                'query' => [
                    'url' => $url
                ]
            ]);

            $result = json_decode($response->getBody(), true);

            if (!empty($result)) {
                $entry = current($result);

                return $entry['attachment_id'];
            }
            return false;
        } catch (GuzzleException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function getErrorsNumber($from, $to)
    {
        try {
            $response = $this->getClient()->request('GET', 'wp/v2/get_errors_number/', [
                'query' => [
                    'from' => $from,
                    'to'   => $to
                ]
            ]);

            $result = json_decode($response->getBody(), true);

            return $result['errors_number'];
        } catch (GuzzleException $e) {

            return false;
        }
    }

    public function getViewsCount($page = 1)
    {

        $response = $this->getClient()->request('GET', 'wp/v2/get_views_count/', [
            'query' => [
                'page' => $page
            ]
        ]);

        $result = json_decode($response->getBody(), true);

        return $result;
    }
}
