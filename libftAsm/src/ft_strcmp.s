section .text
global _ft_strcmp
_ft_strcmp:
	cmp rdi, 0
	je end
	cmp rsi, 0
	je end2

loop:
	cmp rdi, rsi
	je loopinc
	cmp rdi, 0
	je end
	cmp rsi, 0
	je end2
	jmp false

loopinc:
	inc rdi
	inc rsi
	inc rdx
	jmp loop

false:
	sub rdi, rsi
	mov rax, rdi
	ret

end2:
	cmp rdi, 0
	jne false
	jmp ok

end:
	cmp rsi, 0
	jne false

ok:
	mov rax, 0
	ret