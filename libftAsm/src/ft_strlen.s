global _ft_strlen

section .text

_ft_strlen:
	push rbp ;init la stack
	mov rbp, rsp
	mov rax, 0
	cmp rdi, 0
	je end
	mov rcx, -1 ;rcx est décrementé à chaque tour (tant que rcx != 0 car rcx est cense etre la size de la chaine (i < size))
	mov al, 0
	cld
	repne scasb
	not rcx
	dec rcx
	mov rax, rcx
	leave
	ret

end:
	leave
	ret