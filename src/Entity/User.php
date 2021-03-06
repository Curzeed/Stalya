<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"username"}, message="There is already an account with this username")
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="date")
     */
    private $birth_date;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVerified = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tokenMail;

    /**
     * @ORM\Column(type="integer" , nullable=true)
     */
    private $fail;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $last_attempt;

    /**
     * @ORM\Column(type="string",length=450 ,nullable=true)
     */
    private $discord_id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tokenDiscord;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nb_try;

    /**
     * @ORM\ManyToOne(targetEntity=Session::class, inversedBy="user")
     */
    private $session;

    /**
     * @ORM\Column(type="string", length=500, nullable=true)
     */
    private $imgDiscord;

    /**
     * @ORM\OneToOne(targetEntity=History::class, mappedBy="user", cascade={"persist", "remove"})
     */
    private $history;

    /**
     * @ORM\OneToMany(targetEntity=History::class, mappedBy="user")
     */
    private $histories;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $username_discord;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $Admis;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $background_url;

    public function __construct()
    {
        $this->histories = new ArrayCollection();
    }




    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birth_date;
    }

    public function setBirthDate(\DateTimeInterface $birth_date): self
    {
        $this->birth_date = $birth_date;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getTokenMail(): ?string
    {
        return $this->tokenMail;
    }

    public function setTokenMail(?string $tokenMail): self
    {
        $this->tokenMail = $tokenMail;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFail()
    {
        return $this->fail;
    }

    /**
     * @param mixed $fail
     */
    public function setFail($fail): void
    {
        $this->fail = $fail;
    }


    public function getLastAttempt(): ?\DateTimeInterface
    {
        return $this->last_attempt;
    }

    public function setLastAttempt(?\DateTimeInterface $last_attempt): self
    {
        $this->last_attempt = $last_attempt;

        return $this;
    }

    public function getDiscordId(): ?string
    {
        return $this->discord_id;
    }

    public function setDiscordId(?string $discord_id): self
    {
        $this->discord_id = $discord_id;

        return $this;
    }

    public function getTokenDiscord(): ?string
    {
        return $this->tokenDiscord;
    }

    public function setTokenDiscord(?string $tokenDiscord): self
    {
        $this->tokenDiscord = $tokenDiscord;

        return $this;
    }

    public function getNbTry(): ?int
    {
        return $this->nb_try;
    }

    public function setNbTry(?int $nb_try): self
    {
        $this->nb_try = $nb_try;

        return $this;
    }

    /**
     * @return bool
     */
    public function canParticipate(): bool
    {
        $atmDate = new \DateTime('now');
        if($this->getLastAttempt() != null){
            $lastAttempt = clone $this->getLastAttempt();
            return date_modify($lastAttempt,'+ 24 hours') < $atmDate;
        }
        return true;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(?Session $session): self
    {
        $this->session = $session;

        return $this;
    }

    public function getImgDiscord(): ?string
    {
        return $this->imgDiscord;
    }

    public function setImgDiscord(?string $imgDiscord): self
    {
        $this->imgDiscord = $imgDiscord;

        return $this;
    }
    public function canSignupToSession(){
        $countBadResponses = 0;
        foreach ($this->getHistories() as $history) {
            if (!$history->getResponse()->getIsCorrect()) {
                $countBadResponses++;
            }
        }
        return $countBadResponses <= 3;
    }

    /**
     * @return Collection|History[]
     */
    public function getHistories(): Collection
    {
        return $this->histories;
    }

    public function addHistory(History $history): self
    {
        if (!$this->histories->contains($history)) {
            $this->histories[] = $history;
            $history->setUser($this);
        }

        return $this;
    }

    public function removeHistory(History $history): self
    {
        if ($this->histories->removeElement($history)) {
            // set the owning side to null (unless already changed)
            if ($history->getUser() === $this) {
                $history->setUser(null);
            }
        }

        return $this;
    }

    public function getUsernameDiscord(): ?string
    {
        return $this->username_discord;
    }

    public function setUsernameDiscord(?string $username_discord): self
    {
        $this->username_discord = $username_discord;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getAdmis(): ?bool
    {
        return $this->Admis;
    }

    public function setAdmis(?bool $Admis): self
    {
        $this->Admis = $Admis;

        return $this;
    }

    public function getBackgroundUrl(): ?string
    {
        return $this->background_url;
    }

    public function setBackgroundUrl(?string $background_url): self
    {
        $this->background_url = $background_url;

        return $this;
    }

}
