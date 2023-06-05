<?php

namespace App\Console\Commands;

use App\Define\CommonDefine;
use App\RepositoryEloquent\NotificationHistory\NotificationHistoryInterface;
use App\RepositoryEloquent\Product\ProductInterface;
use App\RepositoryEloquent\Post\PostInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class AutoPublicContent extends Command
{
    /**
     * @var ProductInterface
     */

    private ProductInterface $productRepository;

    /**
     * @var PostInterface
     */
    private PostInterface $postRepository;

    /**
     * @var NotificationHistoryInterface
     */

    private NotificationHistoryInterface $notificationHistoryRepository;

    /**
     * Create a new job instance.
     * @param ProductInterface $productInterface
     * @param PostInterface $postInterface
     * @parent NotificationHistoryInterface $notificationHistoryRepository
     */
    public function __construct(
        ProductInterface          $productRepository,
        PostInterface             $postRepository,
        NotificationHistoryInterface $notificationHistoryRepository
    ) {
        $this->productRepository = $productRepository;
        $this->postRepository = $postRepository;
        $this->notificationHistoryRepository = $notificationHistoryRepository;
        parent::__construct();
    }

    protected $signature = 'auto_public_content';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check product/post is public or not';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $listProducts = $this->productRepository->getByConditions(
                ['id', 'is_public', 'auto_public', 'date_public'],
                [
                    'auto_public' => 1,
                    'is_public'   => 0,
                ]
            );

            foreach ($listProducts as $product) {
                if (!empty($product->date_public)) {
                    if (Carbon::parse($product->date_public)->format('Y-m-d H:i:00') == Carbon::now()->format('Y-m-d H:i:00')) {
                        $this->productRepository->updateProductPublic(
                            ['public_status' => 1, 'id' => $product->id]
                        );
                    }
                }
            }

            $listPosts = $this->postRepository->getByConditions(
                ['id', 'is_public', 'auto_public', 'date_public'],
                [
                    'auto_public' => 1,
                    'is_public'   => 0,
                ]
            );

            foreach ($listPosts as $post) {
                if (!empty($post->date_public)) {
                    if (Carbon::parse($post->date_public)->format('Y-m-d H:i:00') == Carbon::now()->format('Y-m-d H:i:00')) {
                        $this->postRepository->updatePostPublic(
                            ['public_status' => 1, 'id' => $post->id]
                        );
                    }
                }
            }
        } catch (\Exception $ex) {
            \Log::error('-----------------AUTO-PUBLIC-CONTENT-ERROR---------------');
            \Log::error($ex->getMessage());
            throw $ex;
        }
    }
}
