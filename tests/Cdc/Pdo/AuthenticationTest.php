<?php

class Cdc_Pdo_AuthenticationTest extends DbTestCase
{

    /**
     * @var Cdc_Pdo_Authentication
     */
    protected $a;

    protected $activeId;
    protected $activeToken;

    protected $expiredId;
    protected $expiredToken;

    protected $invalidId;

    protected function setUp()
    {
        $pdo = $this->getConnection()->getConnection();
        $this->a = new Cdc_Pdo_Authentication($pdo);

        $stmt = $pdo->prepare('insert into usuario (nome, email, senha, token, token_validade) values (?, ?, ?, ?, ?)');

        $i = 1;
        $hasher = $this->a->getHasher();
        $stmt->bindValue($i++, 'User with Active Token');
        $stmt->bindValue($i++, 'email@test.com');
        $stmt->bindValue($i++, $hasher->HashPassword('hunter2'));
        $stmt->bindValue($i++, $this->activeToken = $this->a->createToken('token for password recovery'));
        $stmt->bindValue($i++, time() + 1000000); // expiration in the far future

        $stmt->execute();

        $this->activeId = $pdo->lastInsertId();


        $stmt->bindValue(1, 'User with Expired Token');
        $stmt->bindValue(2, 'expired@email.com');
        $stmt->bindValue(4, $this->expiredToken = $this->a->createToken('random string'));
        $stmt->bindValue(5, time() - 1000000); // expiration in the far past

        $stmt->execute();

        $this->expiredId = $pdo->lastInsertId();


        $this->invalidId = '4545454';


    }

    public function testLoginSuccess()
    {
        $this->assertTrue($this->a->login('email@test.com', 'hunter2'));
    }

    public function testLoginFailBecauseOfWrongLogin()
    {
        $this->assertFalse($this->a->login('emeil@test.com', 'hunter2'));
    }

    public function testLoginFailBecauseOfWrongPassword()
    {
        $this->assertFalse($this->a->login('email@test.com', 'hunter3'));
    }

    public function testLoginFailBecauseOfWrongLoginAndPassword()
    {
        $this->assertFalse($this->a->login('emeil@testwrong.com', 'hunter3'));
    }

    public function testLoginByIdAndTokenSuccess()
    {
        $this->AssertTrue($this->a->loginByIdAndToken($this->activeId, $this->activeToken));
    }

    public function testLoginByIdAndTokenFailBecauseOfWrongToken()
    {
        $this->AssertFalse($this->a->loginByIdAndToken($this->activeId, $this->activeToken . 'wrong token'));
    }

    public function testLoginByIdAndTokenFailBecauseOfWrongId()
    {
        $this->AssertFalse($this->a->loginByIdAndToken($this->invalidId, $this->activeToken));
    }


    public function testLoginByIdAndTokenFailBecauseOfExpiredToken()
    {
        $this->AssertFalse($this->a->loginByIdAndToken($this->expiredId, $this->expiredToken));
    }

    public function testLoginByIdSuccess()
    {
        $this->AssertTrue($this->a->loginById($this->activeId));
    }

    public function testLoginByIdFail()
    {
        $this->AssertFalse($this->a->loginById($this->invalidId));
    }

}
