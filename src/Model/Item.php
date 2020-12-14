<?php

namespace PwExam\Model;

use DateTime;

final class Item
{
    /** @var int */
    private $id;

    /** @var string */
    private $title;

    /** @var string */
    private $description;

    /** @var string */
    private $category;

    /** @var int */
    private $price;

    /** @var string */
    private $image;

    /** @var DateTime */
    private $createdAt;

    /** @var DateTime */
    private $activityTime;

    public function __construct(
        int $id,
        string $title,
        string $description,
        string $category,
        int $price,
        string $image,
        DateTime $createdAt,
        DateTime $activityTime
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->category = $category;
        $this->price = $price;
        $this->image = $image;
        $this->createdAt = $createdAt;
        $this->activityTime = $activityTime;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }


    public function getCategory(): string
    {
        return $this->category;
    }


    /**
     * @return int
     */
    public function getPrice(): int
    {
        return $this->price;
    }


    /**
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }


    /**
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return DateTime
     */
    public function getActivityTime(): DateTime
    {
        return $this->activityTime;
    }
}
