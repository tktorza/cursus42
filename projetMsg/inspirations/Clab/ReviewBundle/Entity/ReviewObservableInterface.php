<?php

namespace Clab\ReviewBundle\Entity;

use Clab\ReviewBundle\Entity\Review;

interface ReviewObservableInterface
{
    public function addReview(Review $review);
    public function getReviews();
}
