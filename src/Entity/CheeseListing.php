<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\CheeseListingRepository;
use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=CheeseListingRepository::class)
 */

#[ApiResource(
    collectionOperations: [
        "get",
        "post"
    ],
    itemOperations: [
        "get"=>["normalization_context"=>["groups"=>["cheese_listing:read","cheese_listing:item:get"]]],
        "put"
    ],
    shortName: "cheeses",
    attributes: ["pagination_items_per_page"=>5],
    denormalizationContext: ["groups"=>["cheese_listing:write"]],
    normalizationContext: ["groups"=>["cheese_listing:read"]]

)]
#[ApiFilter(BooleanFilter::class,properties: ["isPublished"])]
#[ApiFilter(SearchFilter::class,properties:
    [
        "title"=>"partial",
        "owner"=>"exact",
        "owner.username"=>"partial"
    ])
]
#[ApiFilter(RangeFilter::class,properties: ["price"])]

class CheeseListing
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     maxMessage="Describe your cheese in 50 chars or less"
     * )
     * @ORM\Column(type="string", length=255)
     * @Groups({"cheese_listing:read","cheese_listing:write","user:read","user:write"})
     */
    private ?string $title;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="text")
     * @Groups({"cheese_listing:read"})
     */
    private ?string $description;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="integer")
     * @Groups({"cheese_listing:read","cheese_listing:write","user:read","user:write"})
     */
    private ?int $price;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?\DateTimeInterface $createdAt;

    /**
     * @ORM\Column(type="boolean",options={"default" : true})
     * @Groups({"cheese_listing:read"})
     */
    private ?bool $isPublished = true;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="cheeseListings")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"cheese_listing:read","cheese_listing:write"})
     */
    private $owner;


    public function __construct(string $title = null)
    {
        $this->title = $title;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    /*
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }
    */

    /**
     * @Groups({"cheese_listing:read"})
     */
    public function getShortDescription() : ?string
    {
        if(strlen($this->description ) < 40 ){
            return $this->description;
        }

        return substr($this->description,0,40).'...';

    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }


    /**
     * How long ago cheese added
     * @Groups({"cheese_listing:read"})
     */
    public function getCreatedAtAgo(): string
    {
        return Carbon::instance($this->getCreatedAt())->diffForHumans();

    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }


    public function getIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

        return $this;
    }


    /**
     * @Groups ({"cheese_listing:write","user:write"})
     * @SerializedName("description")
     */
    public function setTextDescription(string $description) : self
    {
        $this->description = nl2br($description);
        return $this;

    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }
}
