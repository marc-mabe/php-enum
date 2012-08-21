### Example

## The const way

    class User
    {
        const INACTIVE = 0;
        const ACTIVE   = 1;
        const DELETED  = 2;

        protected $status = 0;

        public function setStatus($status)
        {
            $intStatus = (int)$status;
            if (!in_array($intStatus, array(self::INACTIVE, self::ACTIVE, self::DELETED))) {
                throw new InvalidArgumentException("Invalid status {$status}");
            }
            $this->status = $intStatus;
        }

        public function getStatus()
        {
            return $this->status;
        }
    }

    $user = new User();
    echo 'Default user status: ' . $user->getStatus() . PHP_EOL;
    $user->setStatus(User::ACTIVE);
    echo 'Changed user status: ' . $user->getStatus() . PHP_EOL;

PRINTS:
Default user status: 0
Changed user status: 1

## The Enum way:

    class UserStatusEnum extends Enum
    {
        const INACTIVE = 0;
        const ACTIVE   = 1;
        const DELETED  = 2;

        // default value
        protected $value = self::INACTIVE;
    }

    class User
    {
        protected $status;

        public function setStatus(UserStatusEnum $status)
        {
            $this->status = $status;
        }
 
        public function getStatus()
        {
            if (!$this->status) {
                // init default status
                $this->status = new UserStatusEnum();
            }
            return $this->status;
        }
    }

    $user = new User();
    echo 'Default user status: ' . $user->getStatus() . '(' . $user->getStatus()->getValue() . ')' . PHP_EOL;
    $user->setStatus(new UserStatusEnum(UserStatusEnum::ACTIVE));
    echo 'Changed user status: ' . $user->getStatus() . '(' . $user->getStatus()->getValue() . ')' . PHP_EOL;

PRINTS:
efault user status: INACTIVE (0)
hanged user status: ACTIVE (1)


### New BSD License

The files in this archive are released under the New BSD License.
You can find a copy of this license in LICENSE.txt file.
