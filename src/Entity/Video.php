<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index as Index;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Entity(repositoryClass="App\Repository\VideoRepository")
 * @ORM\Table(name="videos",
 *      indexes={@Index(name="title_idx", columns={"title"})})
 */
class Video
{
    //public const videoForNotLoggedIn = 113716040; //vimeo video id
    public const videoForNotLoggedInOrNoMembers = 'https://player.vimeo.com/video/113716040'; //vimeo video id
    public const VimeoPath = 'https://player.vimeo.com/video/';
    public const perPage = 5; //pagination items on page
    public const uploadFolder = '/uploads/videos/';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $path;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $duration;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="videos")
     * Issue with forgein key constraint can be solved like this and in the controller
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $category;

    /**
     * @Assert\NotBlank(message="Please upload the video as a MP4 file.")
     * @Assert\File(mimeTypes={"video/mp4"})
     */
    private $uploaded_video;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function getVimeoId(): ?string
    {
        // if upload from local get this path: /uploads/videos/2136396.mp4
        if (strpos($this->path, self::uploadFolder) !== false) {
            return $this->path;
        }
        // else we get ann array and explode and return the last element this path: https://player.vimeo.com/video/289729765
        $array = explode('/', $this->getPath());
        return end($array);
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }
//    ? means that argument can be null or integer in this case
    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getUploadedVideo()
    {
        return $this->uploaded_video;
    }

    public function setUploadedVideo( $uploaded_video): self
    {
        $this->uploaded_video = $uploaded_video;

        return $this;
    }
}
