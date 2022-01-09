<?php

namespace Nodus\Packages\LivewireDatatables\Tests;

use Nodus\Packages\LivewireDatatables\Services\Button;
use Nodus\Packages\LivewireDatatables\Tests\data\models\Post;
use Nodus\Packages\LivewireDatatables\Tests\data\models\User;

class ButtonTest extends TestCase
{
    public function testDefault()
    {
        $user = User::factory()->make();
        $button = new Button('Details', 'users.details', ['id' => ':id']);
        $this->assertEquals('Details', $button->getId());
        $this->assertEquals('Details', $button->getLabel());
        $this->assertEquals('http://localhost/user/' . $user->id, $button->getRoute($user));
        $this->assertEquals('_self', $button->getTarget());
        $this->assertEquals(null, $button->getIcon());
        $this->assertEquals(1, $button->getRenderMode());
    }

    public function testStaticRouteParameter()
    {
        $user = User::factory()->make();
        $button = new Button('Details', 'users.details', ['id' => '5']);
        $this->assertEquals('http://localhost/user/5', $button->getRoute($user));
    }

    public function testDynamicRelationRouteParameter()
    {
        $post = Post::factory()->make();

        $button = new Button('Details', 'users.details', ['id' => ':user.id']);
        $this->assertEquals('http://localhost/user/' . $post->user_id, $button->getRoute($post));
    }

    public function testLinkTarget()
    {
        $button = new Button('Details', 'users.details', ['id' => '5']);
        $button->setTarget('_blank');
        $this->assertEquals('_blank', $button->getTarget());
    }

    public function testLabelIcon()
    {
        $button = new Button('Details', 'users.details', ['id' => '5']);
        $button->setIcon('fa-user');
        $this->assertEquals('fa-user', $button->getIcon());
        $this->assertEquals(2, $button->getRenderMode());

        $button->setIcon('fa-user', false);
        $this->assertEquals('fa-user', $button->getIcon());
        $this->assertEquals(3, $button->getRenderMode());
    }

    public function testCustomClasses()
    {
        $button = new Button('Details', 'users.details', ['id' => '5']);
        $this->assertNull($button->getClasses());
        $button->setClasses(['a', 'b']);
        $this->assertEquals('a b', $button->getClasses());
    }

    public function testConfirmation()
    {
        $button = new Button('Details', 'users.details', ['id' => '5']);
        $button->setConfirmation('a', 'b', 'c', 'd', 'e');
        $this->assertEquals(
            [
                'enable'  => true,
                'text'    => 'a',
                'title'   => 'b',
                'confirm' => 'c',
                'cancel'  => 'd',
                'context' => 'e',
            ],
            $button->getConfirmation()
        );
    }

    public function testCondition()
    {
        $user1 = new User();
        $user1->admin = true;
        $user2 = new User();
        $user2->admin = false;

        $button = new Button('Details', 'users.details', ['id' => '5']);
        $button->setCondition(fn(User $user) => $user->admin);

        $this->assertTrue($button->isAllowedToRender($user1));
        $this->assertFalse($button->isAllowedToRender($user2));
    }
}
