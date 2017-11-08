section .text
global _ft_bzero
_ft_bzero:
	cmp rdi, 0
	je return
	mov rax, rsi
	mov rbx ,rdi
	jmp loop

loop:
	cmp rax, 0
	jle return
	mov byte[rbx], 0
	inc rbx
	dec rax	
	jmp loop

return:
	ret